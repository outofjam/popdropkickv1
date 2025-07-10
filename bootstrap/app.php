<?php

use App\Helpers\ApiResponse;
use App\Models\Wrestler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

// import your helper

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: ['*'], // Trust all proxies (DigitalOcean uses dynamic IPs)
            headers: Request::HEADER_X_FORWARDED_ALL
        );
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->render(function (Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                if ($e->getModel() === Wrestler::class) {
                    // Use your helper to generate a consistent error response
                    return ApiResponse::error('Wrestler not found', 404);
                }

                return ApiResponse::error('Resource not found', 404);
            }

            // For non-JSON requests, let Laravel handle default behavior
            return null;
        });
    })
    ->create();
