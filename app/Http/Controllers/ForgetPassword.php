<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendPasswordResetLinkRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class ForgetPassword extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService)
    {
    }

    public function show()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLinkEmail(SendPasswordResetLinkRequest $request)
    {
        $status = $this->passwordResetService->sendResetLink($request->validated());

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

    public function reset(ResetPasswordRequest $request)
    {
        $status = $this->passwordResetService->resetPassword($request->validated());

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