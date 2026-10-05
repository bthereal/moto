<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Define the named rate limiters used by the API.
     */
    private function configureRateLimiting(): void
    {
        // General API throttle, applied to the whole api group by
        // bootstrap/app.php. Keyed per authenticated user so one noisy client
        // cannot exhaust another's budget, falling back to IP when anonymous.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Token issuance and refresh are credential-handling endpoints, so they
        // get a much tighter limit than the rest of the API. The per-email key
        // mirrors the web login throttle in App\Livewire\Forms\LoginForm; the
        // per-IP key stops a single host spraying many different addresses.
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });
    }
}
