<?php
namespace App\Actions;
use Illuminate\Support\Facades\Auth;

class LoginUserAction{
    public function login($credentials, $request){
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/Home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}