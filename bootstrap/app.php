<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // `guest` routes (login, register, password reset) send signed-in users here.
        $middleware->redirectUsersTo('/home');

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Inertia form submits that hit a limit, a permission check or an expired session go back
        // to the page with a toast, instead of an error page in a modal.
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->header('X-Inertia') || $request->isMethod('GET')) {
                return $response;
            }

            $message = match ($response->getStatusCode()) {
                429 => "You're doing that too often. Please wait a minute and try again.",
                419 => 'Your session expired. Please try again.',
                403 => "You don't have permission to do that.",
                default => null,
            };

            return $message ? back()->with('error', $message) : $response;
        });
    })->create();
