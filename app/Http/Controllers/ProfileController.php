<?php

namespace App\Http\Controllers;

use App\Actions\Profile\UpdateProfileAction;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
    }

    public function profile()
    {
        $user = $this->profileService->getProfileData();

        if (! $user) {
            return redirect()->route('login');
        }

        return Inertia::render('Profile', [
            'user' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request, UpdateProfileAction $action)
    {
        $action->execute(
            $request->user(),
            $request->validated('full_name'),
            $request->file('photo'),
            $request->boolean('remove_photo'),
        );

        return back()->with('status', 'Profile saved.');
    }

    /**
     * Stream a user's profile photo from the private uploads disk. Signed-in users only;
     * the URL carries a version so it can be cached.
     */
    public function photo(User $user)
    {
        $disk = Storage::disk('uploads');
        abort_unless($user->photo_path && $disk->exists($user->photo_path), 404);

        return $disk->response($user->photo_path, 'photo.jpg', [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
