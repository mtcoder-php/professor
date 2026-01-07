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
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // TUZATILDI - route() o'rniga to'g'ridan-to'g'ri URL ←
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            // API requests return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tizimga kirish talab qilinadi'
                ], 401);
            }

            // Web requests redirect to login page
            return redirect('/login'); // ← route() o'rniga direct URL
        });
    })->create();
