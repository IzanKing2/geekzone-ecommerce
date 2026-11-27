<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;


class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        summary: "Registrar un usuario",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "surname", "username", "email", "password", "password_confirmation", "recaptcha_token"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Prueba"),
                new OA\Property(property: "surname", type: "string", example: "Prueba"),
                new OA\Property(property: "username", type: "string", example: "Prueba"),
                new OA\Property(property: "email", type: "string", example: "prueba@prueba.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "prueba123"),
                new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "prueba123"),
                new OA\Property(property: "recaptcha_token", type: "string", description: "Token de reCAPTCHA v2 (g-recaptcha-response). Obligatorio salvo que RECAPTCHA_ENABLED=false")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Usuario registrado correctamente")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'recaptcha_token' => ['bail', new Recaptcha],
        ], [
            // Mensajes de error personalizados en español
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Ese nombre de usuario ya está en uso.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // El rol no es asignable en masa: el valor por defecto de la BD es 'user' (cliente)
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // Generar token JWT para el usuario recién creado
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => '¡Usuario registrado correctamente!',
            'user' => $user,
            'token' => $token,
            'type' => 'Bearer',
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        summary: "Iniciar sesión",
        description: "Devuelve un token JWT para usar en rutas protegidas. Usar en el botón Authorize",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password", "recaptcha_token"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "admin@geekzone.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "admin123"),
                new OA\Property(property: "recaptcha_token", type: "string", description: "Token de reCAPTCHA v2 (g-recaptcha-response). Obligatorio salvo que RECAPTCHA_ENABLED=false")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login exitoso. Copia el token y úsalo en Authorize",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "¡Inicio de sesión exitoso!"),
                new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGci.."),
                new OA\Property(property: "type", type: "string", example: "Bearer")
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Error de Validación")]
    #[OA\Response(response: 401, description: "Credenciales incorrectas")]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'recaptcha_token' => ['bail', new Recaptcha],
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del email no es válido.',
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
            'token' => $token,
            'type' => 'Bearer',
        ]);
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Cerrar sesión",
        description: "Invalida el token JWT del usuario.",
        tags: ["Auth"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Sesión cerrada correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    public function logout()
    {
        // Mete el token en la lista negra: deja de ser válido aunque no haya caducado
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
