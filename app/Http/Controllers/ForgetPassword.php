<?php

namespace App\Http\Controllers;

use App\Actions\Password\ResetPasswordAction;
use App\Actions\Password\SendPasswordResetLinkAction;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendPasswordResetLinkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class ForgetPassword extends Controller
{
    public function sendResetLinkEmail(SendPasswordResetLinkRequest $request, SendPasswordResetLinkAction $action)
    {
        $status = $action->execute($request->validated());

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

    public function reset(ResetPasswordRequest $request, ResetPasswordAction $action)
    {
        $status = $action->execute($request->validated());

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

    public function show()
    {
        return Inertia::render('Auth/ForgotPassword');
    }
}
