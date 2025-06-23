<?php

namespace App\Http\Controllers;

use App\Actions\RegisterUserAction;
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
    public function handleGoogleCallback(RegisterUserAction $registerUserAction)
    { 
        return $registerUserAction->googleRegister();
 
    }
}