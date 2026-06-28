<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Web global middleware
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // API global middleware
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // CSRF dan istisno qilingan routelar
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'v1/*',
        ]);

        // Trusted proxies (Cloudflare uchun)
        $middleware->trustProxies(at: '*', headers:
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        // Aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuth::class,
            'proxy.auth' => \App\Http\Middleware\ProxyKeyAuth::class,
            'proxy.ratelimit' => \App\Http\Middleware\RateLimitProxy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Production da xato ma'lumotlarini yashirish
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->is('v1/*') || $request->expectsJson()) {
                if (app()->environment('production')) {
                    return response()->json([
                        'error' => [
                            'message' => 'An error occurred',
                            'type' => 'internal_error',
                        ],
                    ], 500);
                }
            }
        });
    })->create();