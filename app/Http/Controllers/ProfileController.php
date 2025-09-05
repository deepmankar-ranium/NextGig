<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
    }

    public function profile()
    {
        $user = $this->profileService->getProfileData();

        if (!$user) {
            return redirect()->route('login');
        }

        return Inertia::render('Profile', [
            'user' => $user,
        ]);
    }
}
