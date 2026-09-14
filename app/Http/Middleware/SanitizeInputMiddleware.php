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
        // 1. Sanitize all string inputs recursively, excluding password fields
        $input = $request->all();
        array_walk_recursive($input, function (&$val, $key) {
            if (is_string($val)) {
                // Skip any field representing passwords to prevent corruption (CWE-20)
                if (stripos($key, 'password') !== false) {
                    return;
                }

                // Preserve safe formatting for question editor fields
                $richFields = ['question_text', 'explanation', 'instructions'];
                if (in_array($key, $richFields, true)) {
                    $allowedTags = '<p><br><b><strong><i><em><u><s><sub><sup><ul><ol><li><table><thead><tbody><tr><th><td><span><div><code><pre><blockquote><h1><h2><h3><h4><h5><h6>';
                    $val = strip_tags($val, $allowedTags);
                    // Strip dangerous inline event attributes like onclick, onerror, onload
                    $val = preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $val);
                    return;
                }

                // Strip tags completely for all other standard fields to prevent HTML/script injection
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
        $response->headers->set('Permissions-Policy', 'camera=*, microphone=(), geolocation=()');

        // Configure Content Security Policy (CSP) with request-scoped nonce
        $nonce = app('csp-nonce');
        $csp = "default-src 'self'; " .
               "script-src 'self' 'nonce-{$nonce}' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://challenges.cloudflare.com https://sdk.cashfree.com; " .
               "script-src-attr 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
               "img-src 'self' data: https://*.cashfree.com; " .
               "connect-src 'self' https://challenges.cloudflare.com https://api.cashfree.com https://sandbox.cashfree.com; " .
               "frame-src 'self' https://challenges.cloudflare.com https://sdk.cashfree.com; " .
               "worker-src 'self'; " .
               "manifest-src 'self';";
               
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
