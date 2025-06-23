<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Models\Employer;
use App\Models\JobSeeker;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Actions\RegisterUserAction;
use App\Http\Requests\LoginUserRequest;
use App\Actions\LoginUserAction;

class RegisterUserController extends Controller
{
    public function show()
    {
        $roles = Role::all(); // Fetch all roles from the database

        
        return Inertia::render('Register', [
            'roles' => $roles
        ]);
    }

     public function storeRole(Request $request)
     {
         $request->validate([
             'role_id' => 'required|integer|in:1,2,3',
         ]);
     
         session(['user_role' => $request->role_id]);
     
         return redirect()->route('register-2'); // Use named route instead of URL
     }
     
     public function showRegister2(){
        $role_id = session('user_role'); // Retrieve role_id from session

    
        // Ensure role selection is required and valid
        if (!$role_id) {
            return redirect('/register')->withErrors(['role_id' => 'You must select a valid role before registering.']);
        }
     
   
        return Inertia::render('Register2');
     }

     public function register(RegisterUserRequest $request, registerUserAction $registerUserAction)
     {
         $role_id = session('user_role'); // Retrieve role_id from session
     
         if (!$role_id) {
             return back()->withErrors(['role_id' => 'Role selection is required.']);
         }
     
         $validatedData = $request->validated();

         $registerUserAction->registerUser($request, $role_id, $validatedData);
         // Clear the session role_id after registration
         session()->forget('user_role');
     
         return redirect('/Home');
     }
     
    

    public function logIn()
    {
        return Inertia::render('LogIn');
    }

    public function authenticate(LoginUserRequest $request, LoginUserAction $loginUserAction)
    {
        $credentials = $request->validated();

        return $loginUserAction->login($credentials, $request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
