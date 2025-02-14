<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employer;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterUserController extends Controller
{
    public function show()
    {
        $roles = [
            ['id' => 1, 'name' => 'Admin'],
            ['id' => 2, 'name' => 'Employer'],
            ['id' => 3, 'name' => 'JobSeeker'],
        ];
        
        return Inertia::render('Register', [
            'roles' => $roles
        ]);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|integer',
            'name' => 'nullable|string|max:255', // Employer name
            'address' => 'nullable|string|max:255', // Employer address
            'phone' => 'nullable|string|max:20', // Employer phone
            'description' => 'nullable|string', // Employer description
        ]);
    
        // Hash password
        $validatedData['password'] = Hash::make($validatedData['password']);
        
        // Create User
        $user = User::create([
            'full_name' => $validatedData['full_name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role_id' => $validatedData['role_id'],
        ]);
    
        // Insert into `employers` if role_id is 2
        if ($user->role_id == 2) {
            Employer::create([
                'user_id' => $user->id, // Ensure user_id is passed
                'name' => $request->input('name', $user->full_name),
                'email' => $user->email,
                'address' => $request->input('address', 'Not Provided'),
                'phone' => $request->input('phone', null),
                'description' => $request->input('description', null),
            ]);
        }
    
        // Insert into `job_seekers` if role_id is 3
        elseif ($user->role_id == 3) {
            JobSeeker::create([
                'user_id' => $user->id,
                'name' => $request->input('name', $user->full_name),
                'resume_link' => $request->input('resume_link', null),
            ]);
        }
    
        // Log in user
        Auth::login($user);
    
        return redirect('/Home');
    }
    

    public function logIn()
    {
        return Inertia::render('LogIn');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/Home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
