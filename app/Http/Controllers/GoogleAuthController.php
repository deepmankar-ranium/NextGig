<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employer;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // Step 1: Redirect user to Google OAuth
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Step 2: Handle Google OAuth Callback
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $role_id = session('user_role'); // Retrieve role_id from session
            // Check if user already exists in our database
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Store new user in database with a default role (change if needed)
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(str()->random(16)), // Random password since Google handles authentication
                    'role_id' => $role_id, // Assign role_id from session
                    'google_id' => $googleUser->getId(), // Store Google ID for reference
                ]);

                // Insert into `employers` if role_id is 2
                if ($role_id == 2) {
                    Employer::create([
                        'user_id' => $user->id,
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'address' => 'Not Provided',
                        'phone' => null,
                        'description' => null,
                    ]);
                }
                // Insert into `job_seekers` if role_id is 3
                elseif ($role_id == 3) {
                    JobSeeker::create([
                        'user_id' => $user->id,
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'resume_link' => null,
                    ]);
                }
            }

            // Log in the user
            Auth::login($user);

            return redirect('/Home');
        } catch (\Exception $e) {
            return redirect('/register')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
