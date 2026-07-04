<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Sanitize all string inputs recursively
        $input = $request->all();
        array_walk_recursive($input, function (&$val) {
            if (is_string($val)) {
                // Strip tags completely to prevent any HTML/script injection
                $val = strip_tags($val);
            }
        });
        $request->merge($input);

        // 2. Process request
        $response = $next($request);

        // 3. Add secure HTTP headers to the response
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        // Configure Content Security Policy (CSP)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://challenges.cloudflare.com https://checkout.razorpay.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https://ui-avatars.com https://*.razorpay.com; " .
               "connect-src 'self' https://challenges.cloudflare.com https://api.razorpay.com; " .
               "frame-src 'self' https://challenges.cloudflare.com https://api.razorpay.com https://checkout.razorpay.com;";
               
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
