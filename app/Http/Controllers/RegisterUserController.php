<?php

namespace App\Http\Controllers;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Requests\AuthenticationRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisterUserController extends Controller
{
    public function show()
    {
        $roles = [
            ['id' => 2, 'name' => 'Employer'],
            ['id' => 3, 'name' => 'JobSeeker'],
        ];

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

        return redirect()->route('register-2');
    }

    public function showRegister2()
    {
        $role_id = session('user_role');

        if (!$role_id) {
            return redirect('/register')->withErrors(['role_id' => 'You must select a valid role before registering.']);
        }

        return Inertia::render('Register2');
    }

    public function register(RegisterUserRequest $request, RegisterUserAction $registerUserAction)
    {
        $role_id = session('user_role');

        if (!$role_id) {
            return back()->withErrors(['role_id' => 'Role selection is required.']);
        }

        $registerUserAction->execute($request->validated(), $role_id);

        return redirect('/Home');
    }

    public function logIn()
    {
        return Inertia::render('LogIn');
    }

    public function authenticate(AuthenticationRequest $request, AuthenticateUserAction $authenticateUserAction)
    {
        $authenticateUserAction->execute($request->only('email', 'password'), $request->boolean('remember'));

        return redirect()->intended('/Home');
    }

    public function logout(Request $request, LogoutUserAction $logoutUserAction)
    {
        $logoutUserAction->execute($request);

        return redirect('/');
    }
}