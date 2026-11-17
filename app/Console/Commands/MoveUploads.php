<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-off: copy uploads saved on the local disk (before switching UPLOADS_DISK to s3) into the
 * uploads disk. Paths are kept, so database rows don't change. Safe to run more than once.
 */
class MoveUploads extends Command
{
    protected $signature = 'uploads:copy-local {--delete : Delete each local file after it is copied}';

    protected $description = 'Copy resumes, application PDFs and photos from storage/app/private to the uploads disk (S3/MinIO)';

    private const FOLDERS = ['resumes', 'applications', 'avatars'];

    public function handle(): int
    {
        if (config('filesystems.disks.uploads.driver') === 'local') {
            $this->warn('UPLOADS_DISK is local, so there is nothing to copy. Set UPLOADS_DISK=s3 first.');

            return self::FAILURE;
        }

        $from = Storage::build(['driver' => 'local', 'root' => storage_path('app/private')]);
        $to = Storage::disk('uploads');
        $copied = $skipped = 0;

        foreach (self::FOLDERS as $folder) {
            foreach ($from->allFiles($folder) as $path) {
                if ($to->exists($path)) {
                    $skipped++;
                } else {
                    $to->writeStream($path, $from->readStream($path));
                    $copied++;
                }

                if ($this->option('delete')) {
                    $from->delete($path);
                }
            }
        }

        $this->info("Copied {$copied} file(s); {$skipped} already there.");

        return self::SUCCESS;
    }
}
