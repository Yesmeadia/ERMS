<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('csp-nonce', function () {
            return base64_encode(random_bytes(16));
        });

        // Bind custom password broker manager to use BcryptDatabaseTokenRepository (CWE-256)
        $this->app->singleton('auth.password', function ($app) {
            return new \App\Auth\Passwords\CustomPasswordBrokerManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS scheme for all generated URLs when APP_URL is HTTPS.
        // Required on Hostinger/shared hosting where SSL is terminated at the
        // load balancer. Without this, APP_URL=https://... is set but Laravel
        // still generates http:// redirect URLs (login, dashboard, etc.).
        // The host's server then force-redirects those to https://, causing
        // ERR_TOO_MANY_REDIRECTS. forceScheme('https') ensures all route()
        // and redirect() calls produce https:// URLs.
        if (str_starts_with(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Blade::directive('nonce', function () {
            return 'nonce="<?php echo app(\'csp-nonce\'); ?>"';
        });

        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        // Dedicated stricter limiter for MFA verification: 3 attempts/min per IP.
        // Brute-forcing a 6-digit TOTP at 5/min = 7,200/day. At 3/min = 4,320/day.
        // Combined with reuse detection and audit logging this makes brute-force impractical.
        \Illuminate\Support\Facades\RateLimiter::for('mfa', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by($request->ip());
        });

        // Public hall-ticket verification portal rate limiter.
        // 10 lookups/min per IP prevents automated enumeration of hall ticket numbers.
        // Even with random tokens, rate-limiting is a mandatory defence-in-depth layer (CWE-330).
        \Illuminate\Support\Facades\RateLimiter::for('verification', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)
                ->by($request->ip())
                ->response(function () {
                    return response()->view('errors.429', [], 429);
                });
        });
    }
}
