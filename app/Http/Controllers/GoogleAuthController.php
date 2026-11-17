<?php

namespace App\Http\Controllers;

use App\Actions\Auth\HandleGoogleCallbackAction;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect('/login')->withErrors(['google' => 'Google sign-in is not configured on this server.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(HandleGoogleCallbackAction $action)
    {
        return match ($action->execute()) {
            'logged_in' => redirect()->intended('/home'),
            'needs_role' => redirect('/register')->with('status', 'Almost there: choose how you\'ll use NextGig to finish signing up with Google.'),
            default => redirect('/login')->withErrors(['google' => 'Google sign-in didn\'t work. Please try again, or use your email and password.']),
        };
    }
}
