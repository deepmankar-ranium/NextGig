<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employer;
use App\Models\JobSeeker;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthService
{
    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $role_id = session('user_role');

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(str()->random(16)),
                    'role_id' => $role_id,
                    'google_id' => $googleUser->getId(),
                ]);

                if ($role_id == 2) {
                    Employer::create([
                        'user_id' => $user->id,
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'address' => 'Not Provided',
                        'phone' => null,
                        'description' => null,
                    ]);
                } elseif ($role_id == 3) {
                    JobSeeker::create([
                        'user_id' => $user->id,
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'resume_link' => null,
                    ]);
                }
            }

            Auth::login($user);

            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }
}
