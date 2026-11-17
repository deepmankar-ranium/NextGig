<?php

namespace App\Http\Controllers;

use App\Actions\Auth\HandleGoogleCallbackAction;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect('/login')->withErrors(['google' => 'Google sign-in is not configured on this server.']);
        }

        // Google returns to the configured callback host. Sign-in must start on that same host, or
        // the browser sends a different session cookie back (e.g. 127.0.0.1 vs localhost) and the
        // OAuth state check fails. So hop to the callback's origin first.
        $callbackOrigin = $this->origin((string) config('services.google.redirect'));
        if ($callbackOrigin && $callbackOrigin !== $this->origin($request->getSchemeAndHttpHost())) {
            return redirect()->away($callbackOrigin.'/auth/google');
        }

        return Socialite::driver('google')->redirect();
    }

    /** scheme://host[:port] of an absolute URL, or null for a relative one. */
    private function origin(string $url): ?string
    {
        $parts = parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $port = $parts['port'] ?? null;
        $default = ['http' => 80, 'https' => 443][strtolower($parts['scheme'])] ?? null;

        return strtolower($parts['scheme']).'://'.strtolower($parts['host']).($port && $port !== $default ? ':'.$port : '');
    }

    public function handleGoogleCallback(HandleGoogleCallbackAction $action)
    {
        return match ($action->execute()) {
            'logged_in' => redirect()->intended('/home'),
            'needs_role' => redirect('/register')->with('status', 'Almost there: choose how you\'ll use NextGig to finish signing up with Google.'),
            default => redirect('/login')->withErrors(['google' => 'Google sign-in didn\'t work. Please try again, or use your email and password.']),
        };
    }
}
