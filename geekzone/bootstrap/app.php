<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
            'admin' => \App\Http\Middleware\CheckAdminRole::class,
        ]);

        // Límite general para todas las rutas /api (limitador "api" en AppServiceProvider)
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // La API siempre responde JSON (errores de validación, 404, 429…), aunque el
        // cliente no envíe "Accept: application/json"; si no, Laravel redirige con 302.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
