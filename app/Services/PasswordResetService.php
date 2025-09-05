<?php

namespace App\Services;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function sendResetLink(array $data)
    {
        return Password::sendResetLink($data);
    }

    public function resetPassword(array $data)
    {
        return Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();
        });
    }
}
