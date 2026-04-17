<?php

namespace App\Http\Controllers\Api;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "GeekZone API",
    title: "GeekZone API"
)]
#[OA\Server(
    url: "/",
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Introduce el token JWT"
)]
abstract class Controller
{
    /**
     * Respuesta JSON de éxito estandarizada.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $status = 200)
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
     *
     * @param string $message
     * @param int $status
     * @param array|null $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $status = 400, ?array $errors = null)
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
     * @param \Illuminate\Contracts\Support\MessageBag|array $errors
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse($errors, string $message = 'Error de validación.', int $status = 422)
    {
        return $this->errorResponse($message, $status, is_array($errors) ? $errors : $errors->toArray());
    }
}
