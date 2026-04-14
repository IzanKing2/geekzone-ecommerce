<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'surname'  => 'required|string|max:255',
            'username'  => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            // Mensajes de error personalizados en español
            'name.required'      => 'El nombre es obligatorio.',
            'surname.required'   => 'El apellido es obligatorio.',
            'username.required'  => 'El nombre de usuario es obligatorio.',
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
            'surname'  => $request->surname,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,
            'rol'      => 'user', // Por defecto, todos son clientes
        ]);

        // Generar token JWT para el usuario recién creado
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => '¡Usuario registrado correctamente!',
            'user' => $user,
            'token'   => $token,
            'type'    => 'Bearer',
        ], 201);
    }


    public function login(Request $request)
    {
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
        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Credenciales incorrectas. Verifica tu email y contraseña.',
            ], 401);
        }

        $token = JWTAuth::fromUser(Auth::user());

        return response()->json([
            'message' => '¡Inicio de sesión exitoso!',
            'user' => Auth::user(),
            'token'   => $token,
            'type'    => 'Bearer',
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
