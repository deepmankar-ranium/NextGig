<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\Employer;
use App\Models\JobSeeker;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    public function execute(array $data, int $role_id): User
    {
        $data['password'] = Hash::make($data['password']);

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $role_id,
        ]);

        if ($role_id == 2) {
            Employer::create([
                'user_id' => $user->id,
                'name' => $data['name'] ?? $user->full_name,
                'email' => $user->email,
                'address' => $data['address'] ?? 'Not Provided',
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        } elseif ($role_id == 3) {
            JobSeeker::create([
                'user_id' => $user->id,
                'name' => $data['name'] ?? $user->full_name,
                'email' => $user->email,
                'resume_link' => $data['resume_link'] ?? null,
            ]);
        }

        event(new UserRegistered($user));

        Auth::login($user);

        session()->forget('user_role');

        return $user;
    }
}
