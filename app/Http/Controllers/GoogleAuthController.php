<?php

namespace App\Http\Controllers;

use App\Services\GoogleAuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(private GoogleAuthService $googleAuthService)
    {
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = $this->googleAuthService->handleCallback();

        if ($user) {
            return redirect('/Home');
        } else {
            return redirect('/register')->with('error', 'Something went wrong. Please try again.');
        }
    }
}