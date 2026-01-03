<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $csp = "script-src 'self' 'unsafe-inline' https://pagead2.googlesyndication.com;";
        $response->headers->set('Content-Security-Policy', $csp);
        return $response;
    }
}
