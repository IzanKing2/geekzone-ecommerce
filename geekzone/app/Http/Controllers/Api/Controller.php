<?php

namespace App\Http\Controllers\Api;

use App\Http\Concerns\ApiResponses;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'GeekZone API',
    title: 'GeekZone API'
)]
#[OA\Server(
    url: '/',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Introduce el token JWT'
)]
abstract class Controller
{
    // successResponse(), errorResponse() y validationErrorResponse()
    use ApiResponses;
}
