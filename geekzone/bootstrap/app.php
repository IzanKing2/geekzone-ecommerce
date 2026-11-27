<?php

use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => JwtMiddleware::class,
            'admin' => CheckAdminRole::class,
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

        // Los errores que genera el propio framework (404, 405, 429, 401, validación…) usan el
        // mismo formato { success, message, data, errors } que el resto de la API. Se conservan
        // las cabeceras (p. ej. Retry-After) y, con APP_DEBUG=true, el 500 mantiene su traza.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

            [$status, $message, $errors] = match (true) {
                $e instanceof ValidationException => [422, 'Error de validación.', $e->errors()],
                $e instanceof AuthenticationException => [401, 'No autenticado.', null],
                $e instanceof ThrottleRequestsException => [429, 'Demasiadas peticiones. Inténtalo de nuevo en unos instantes.', null],
                $e instanceof HttpExceptionInterface => [
                    $e->getStatusCode(),
                    match ($e->getStatusCode()) {
                        403 => 'No tienes permisos para realizar esta acción.',
                        404 => 'Recurso no encontrado.',
                        405 => 'Método no permitido.',
                        default => $e->getMessage() ?: 'Error en la petición.',
                    },
                    null,
                ],
                default => [null, null, null],
            };

            if ($status === null) {
                // Error inesperado (500): en producción no se filtra ningún detalle
                if (config('app.debug')) {
                    return null;
                }

                [$status, $message] = [500, 'Error interno del servidor.'];
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => $errors,
            ], $status, $headers);
        });
    })->create();
