<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgetPassword extends Controller
{
    public function show(){
        return Inertia::render('Auth/ForgotPassword');
    }
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status == Password::RESET_LINK_SENT) {
            return redirect('/forgot-password')->with('status', __($status));
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return Inertia::render('Auth/ResetPassword', [
                'status' => __($status),
                'email' => $request->email,
                'token' => $request->token
            ]);
        }

        $errorField = $status == Password::INVALID_TOKEN ? 'token' : 'email';

        throw \Illuminate\Validation\ValidationException::withMessages([
            $errorField => [__($status)],
        ]);
    }
}
