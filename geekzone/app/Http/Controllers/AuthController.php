<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validador = Validator::make($request->all(), [
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

        if ($validador->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validador->errors(),
            ], 422);
        }

        $usuario = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'rol'      => 'user', // Por defecto, todos son clientes
        ]);

        // Generar token JWT para el usuario recién creado
        $token = JWTAuth::fromUser($usuario);

        return response()->json([
            'mensaje' => '¡Usuario registrado correctamente!',
            'usuario' => $usuario,
            'token'   => $token,
            'tipo'    => 'Bearer',
        ], 201);
    }

    
    public function login(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'El formato del email no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if ($validador->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validador->errors(),
            ], 422);
        }

        // Intentar autenticar con email y password
        $credenciales = $request->only('email', 'password');
        $token = Auth::attempt($credenciales);

        if (!$token) {
            return response()->json([
                'mensaje' => 'Credenciales incorrectas. Verifica tu email y contraseña.',
            ], 401);
        }

        $token = JWTAuth::fromUser(Auth::user());

        return response()->json([
            'mensaje' => '¡Inicio de sesión exitoso!',
            'usuario' => Auth::user(),
            'token'   => $token,
            'tipo'    => 'Bearer',
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente.',
        ]);
    }
}
