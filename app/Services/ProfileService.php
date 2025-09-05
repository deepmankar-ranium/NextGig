<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class ProfileService
{
    public function getProfileData()
    {
        $user = Auth::user();
        if ($user) {
            $user->load('role');
        }
        return $user;
    }
}
