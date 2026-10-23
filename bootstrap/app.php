<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ngrok/reverse proxy terminasi HTTPS; tanpa ini asset() jadi http:// (mixed content)
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->append(\App\Http\Middleware\TrustNgrok::class);
        // Paksa JSON response untuk request API yang tidak terautentikasi
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null; // Jangan redirect, biarkan middleware Authenticate balas JSON
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Hostinger: tanpa folder public di dalam aplikasi, isi public ada di public_html (sejajar dengan folder aplikasi)
if (! is_dir(dirname(__DIR__).'/public') && is_dir($publicHtml = dirname(__DIR__, 2).'/public_html')) {
    $app->usePublicPath($publicHtml);
}

return $app;
