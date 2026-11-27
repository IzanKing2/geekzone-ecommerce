<?php

namespace App\Http\Concerns;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\JsonResponse;

/**
 * Formato único de respuesta de la API: { success, message, data, errors }.
 *
 * Lo usan los controladores (vía Api\Controller) y los middlewares, para que
 * cualquier respuesta JSON de /api tenga la misma forma, también los 401 y 403.
 */
trait ApiResponses
{
    /**
     * Respuesta JSON de éxito estandarizada.
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    /**
     * Respuesta JSON de error estandarizada.
     */
    protected function errorResponse(string $message, int $status = 400, ?array $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Respuesta JSON específica para errores de validación.
     *
     * @param  MessageBag|array  $errors
     */
    protected function validationErrorResponse($errors, string $message = 'Error de validación.', int $status = 422): JsonResponse
    {
        return $this->errorResponse($message, $status, is_array($errors) ? $errors : $errors->toArray());
    }
}
