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

        if (!$user) {
            return redirect()->route('login');
        }

        // Eager load the role relationship to avoid N+1 issues
        $user->load('role');

        return Inertia::render('Profile', [
            'user' => $user,
        ]);
    }
}