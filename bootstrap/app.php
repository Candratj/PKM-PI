<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Konfigurasi middleware dasar tanpa Sanctum
        $middleware->api(prepend: [
            // Kosongkan array jika tidak ada middleware yang ingin ditambahkan
        ]);

        $middleware->alias([
            // Tidak perlu mendefinisikan alias middleware verified
        ]);

        // Disable CSRF untuk route API jika diperlukan
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => class_basename($e)
                ], 500);
            }
        });
    })->create();