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

        // Paksa HTTPS di production. Ngrok tidak perlu: trustProxies (bootstrap/app.php) membaca X-Forwarded-Proto,
        // sedangkan force di sini membuat asset di http://localhost ikut https dan gagal dimuat.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('username').'|'.$request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                ], 429);
            });
        });
    }
}
