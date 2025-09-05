<?php

namespace App\Http\Controllers;

use App\Actions\Auth\HandleGoogleCallbackAction;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(HandleGoogleCallbackAction $action)
    {
        $user = $action->execute();

        if ($user) {
            return redirect('/Home');
        } else {
            return redirect('/register')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
