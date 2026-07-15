<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies — required for Hostinger/shared hosting where SSL is
        // terminated at the load balancer. Without this, Laravel sees HTTP and
        // redirects to HTTPS in an infinite loop (ERR_TOO_MANY_REDIRECTS).
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'payments/webhook',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInputMiddleware::class,
            \App\Http\Middleware\SecurityShieldMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \Sentry\Laravel\Integration::handles($exceptions);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

// Customize public path for shared hosting (Hostinger) when ERMS and public_html structure is used
$basePath = $app->basePath();
$parentPath = dirname($basePath);
if (basename($basePath) === 'ERMS' || (is_dir($parentPath . '/public_html') && !is_dir($basePath . '/public'))) {
    $app->usePublicPath($parentPath . '/public_html');
}

return $app;
