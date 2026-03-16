<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ], [
                // Mensajes de error personalizados en español
                'name.required'      => 'El nombre es obligatorio.',
                'email.required'     => 'El email es obligatorio.',
                'email.email'        => 'El formato del email no es válido.',
                'email.unique'       => 'Este email ya está registrado.',
                'password.required'  => 'La contraseña es obligatoria.',
                'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'      => 'user', // Por defecto, todos son clientes
            ]);

            // Generar token JWT para el usuario recién creado
            $token = JWTAuth::fromUser($user);

            return $this->successResponse(
                [
                    'user'  => $user,
                    'token' => $token,
                    'type'  => 'Bearer',
                ],
                '¡Usuario registrado correctamente!',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al registrar el usuario.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required'    => 'El email es obligatorio.',
                'email.email'       => 'El formato del email no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Intentar autenticar con email y password
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return $this->errorResponse(
                    'Credenciales incorrectas. Verifica tu email y contraseña.',
                    401
                );
            }

            return $this->successResponse(
                [
                    'user'       => JWTAuth::user(),
                    'token'      => $token,
                    'expires_in' => JWTAuth::factory()->getTTL() * 60, // Tiempo de expiración del token en segundos
                    'type'       => 'Bearer',
                ],
                '¡Inicio de sesión exitoso!'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al iniciar sesión.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->successResponse(null, 'Sesión cerrada correctamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al cerrar sesión.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
