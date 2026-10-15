<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrustNgrok
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ngrok skip warning header
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}