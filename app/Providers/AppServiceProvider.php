<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Paksa HTTPS di production (atau saat pakai Ngrok)
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'ngrok')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('username') . '|' . $request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                ], 429);
            });
        });
    }
}