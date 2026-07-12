<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('sanctum', function (Request $request) {

            $tokenId = $request->user()?->currentAccessToken()?->id;

            return Limit::perMinute(config('sanctum.rate_limit_per_minute'))
                ->by($tokenId ?? $request->ip());
        });
    }
}
