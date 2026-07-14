<?php

namespace App\Providers;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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

        // Override the default password broker's token repository hasher to bcrypt.
        // This ensures password reset tokens are stored as strong bcrypt hashes rather than SHA-1 (CWE-256 / CWE-307).
        if (!empty(config('app.key')) && is_string(config('app.key'))) {
            $repository = \Illuminate\Support\Facades\Password::broker()->getRepository();
            if ($repository instanceof \Illuminate\Auth\Passwords\DatabaseTokenRepository) {
                $reflection = new \ReflectionClass($repository);
                $property = $reflection->getProperty('hasher');
                $property->setValue($repository, new \Illuminate\Hashing\BcryptHasher());
            }
        }
    }
}
