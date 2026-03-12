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
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'      => 'user', // Por defecto, todos son clientes
            ]);

            // Generar token JWT para el usuario recién creado
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => '¡Usuario registrado correctamente!',
                'user' => $user,
                'token'   => $token,
                'type'    => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
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
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Intentar autenticar con email y password
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return response()->json([
                    'message' => 'Credenciales incorrectas. Verifica tu email y contraseña.',
                ], 401);
            }

            return response()->json([
                'message' => '¡Inicio de sesión exitoso!',
                'user' => JWTAuth::user(),
                'token'   => $token,
                'expires_in' => JWTAuth::factory()->getTTL() * 60, // Tiempo de expiración del token en minutos
                'type'    => 'Bearer',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Sesión cerrada correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
