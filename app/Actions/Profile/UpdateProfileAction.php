<?php

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateProfileAction
{
    /** Avatars are stored as a square JPEG of this many pixels. */
    public const PHOTO_SIZE = 320;

    public function execute(User $user, string $fullName, ?UploadedFile $photo = null, bool $removePhoto = false): User
    {
        $disk = Storage::disk('uploads');
        $oldPhoto = $user->photo_path;
        $newPhoto = null;

        if ($photo) {
            // A new random name each time, so the photo URL changes and browsers never show a stale one.
            $newPhoto = 'avatars/user-'.$user->id.'-'.Str::random(12).'.jpg';
            $disk->put($newPhoto, $this->squareJpeg($photo));
        }

        DB::transaction(function () use ($user, $fullName, $newPhoto, $removePhoto) {
            $user->full_name = trim($fullName);

            if ($newPhoto) {
                $user->photo_path = $newPhoto;
            } elseif ($removePhoto) {
                $user->photo_path = null;
            }

            $user->save();

            // The seeker record keeps its own copy of the name (shown to employers).
            $user->jobseeker?->update(['name' => $user->full_name]);
        });

        if ($oldPhoto && $oldPhoto !== $user->photo_path) {
            $disk->delete($oldPhoto);
        }

        return $user;
    }

    /**
     * Centre-crop to a square, resize, re-encode as JPEG. Re-encoding also strips EXIF
     * (GPS location, camera details) from the original.
     */
    private function squareJpeg(UploadedFile $file): string
    {
        $src = $this->decode($file);

        $src = $this->applyExifOrientation($src, $file);
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $size = min(self::PHOTO_SIZE, $side);

        $dst = imagecreatetruecolor($size, $size);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // flatten transparency on white
        imagecopyresampled($dst, $src, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), $size, $size, $side, $side);

        ob_start();
        imagejpeg($dst, null, 85);
        $bytes = (string) ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $bytes;
    }

    /**
     * GD reads JPEG/PNG/WebP/GIF/BMP; Imagick (when installed) covers HEIC/AVIF from phones.
     */
    private function decode(UploadedFile $file): \GdImage
    {
        $bytes = (string) file_get_contents($file->getRealPath());
        $img = @imagecreatefromstring($bytes);

        if (! $img && class_exists(\Imagick::class)) {
            try {
                $im = new \Imagick();
                $im->readImageBlob($bytes);
                $im->autoOrient();
                $im->setImageFormat('png');
                $img = @imagecreatefromstring($im->getImageBlob());
                $im->clear();
            } catch (\ImagickException) {
                $img = false;
            }
        }

        if (! $img) {
            throw ValidationException::withMessages(['photo' => "We couldn't read that photo. Try a different one."]);
        }

        return $img;
    }

    private function applyExifOrientation(\GdImage $img, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $img;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;

        return match ((int) $orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }
}
