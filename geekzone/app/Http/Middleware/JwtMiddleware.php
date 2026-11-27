<?php

namespace App\Http\Middleware;

use App\Http\Concerns\ApiResponses;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    use ApiResponses;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verificamos si el token es válido
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return $this->errorResponse('Usuario no encontrado', 401);
            }
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token expirado', 401);
        } catch (TokenInvalidException $e) {
            // Incluye tokens en la lista negra (sesión cerrada)
            return $this->errorResponse('Token inválido', 401);
        } catch (JWTException $e) {
            return $this->errorResponse('Token no encontrado', 401);
        }

        return $next($request);
    }
}
