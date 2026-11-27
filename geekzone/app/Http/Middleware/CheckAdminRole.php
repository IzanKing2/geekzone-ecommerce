<?php

namespace App\Http\Middleware;

use App\Http\Concerns\ApiResponses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    use ApiResponses;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'admin') {
            return $this->errorResponse('Acceso denegado: debes ser administrador para acceder a este recurso', 403);
        }

        return $next($request);
    }
}
