<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class ProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        if ($user) {
            $user->role;
        } else {
            return redirect()->route('login');
        }
        return Inertia::render('Profile', [
            'user' => $user,
        ]);
    }
}