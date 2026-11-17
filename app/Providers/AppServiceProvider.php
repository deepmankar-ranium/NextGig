<?php

namespace App\Providers;

use App\AI\AiProvider;
use App\AI\FakeAiProvider;
use App\AI\GeminiProvider;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\JobListing;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiProvider::class, function () {
            return match (config('ai.provider')) {
                'fake' => new FakeAiProvider(),
                default => new GeminiProvider(
                    config('services.gemini.key'),
                    config('services.gemini.model'),
                    config('services.gemini.base_url'),
                    config('services.gemini.timeout'),
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->configureRateLimiting();

        Gate::define('manage-resume', fn (User $user) => $user->role?->name === 'Job Seeker' && $user->jobseeker !== null);

        Inertia::share('auth', function () {
            return [
                'user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'role_id' => auth()->user()->role_id,
                ] : null,
            ];
        });
    }

    /**
     * login: 5/min per email+IP (slows password guessing).
     * auth:  10/min per IP for registration and password reset.
     * writes: 30/min per user for messages, applications, tags.
     * ai:    per-minute and per-day caps per user for every AI call (each call costs money).
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('writes', fn (Request $request) => Limit::perMinute(30)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('ai', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            $tooMany = function (Request $request, array $headers) {
                $message = "You've reached the AI usage limit. Please try again later.";

                // Inertia form posts need a redirect with errors; XHR/JSON gets a 429.
                return $request->header('X-Inertia')
                    ? back()->withErrors(['ai' => $message])
                    : response()->json(['status' => 'error', 'message' => $message], 429, $headers);
            };

            return [
                Limit::perMinute(config('ai.per_minute'))->by('min:'.$key)->response($tooMany),
                Limit::perDay(config('ai.daily_cap'))->by('day:'.$key)->response($tooMany),
            ];
        });
    }
}
