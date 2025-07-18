<?php

use App\Helpers\ApiResponse;
use App\Models\Championship;
use App\Models\Wrestler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Request;

// import your helper

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: ['*'], // Trust all proxies
            headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                $modelClass = $e->getModel();
                if ($modelClass === Wrestler::class) {
                    // Use your helper to generate a consistent error response
                    return ApiResponse::error('Wrestler not found', 404);
                }

                if ($modelClass === Championship::class) {
                    return ApiResponse::error('Championship not found', 404);
                }
                return ApiResponse::error('Resource not found', 404);
            }

            // For non-JSON requests, let Laravel handle default behavior
            return null;
        });
    })
    ->create();
