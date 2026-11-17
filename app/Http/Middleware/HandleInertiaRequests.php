<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // status: shown inline by the page. success / error: shown as a toast by AppLayout;
            // `id` changes with every new message so the same text twice still toasts twice.
            'flash' => function () use ($request) {
                $flash = [
                    'status' => $request->session()->get('status'),
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                ];
                $flash['id'] = ($flash['success'] || $flash['error']) ? (string) str()->uuid() : null;

                return $flash;
            },
        ];
    }
}
