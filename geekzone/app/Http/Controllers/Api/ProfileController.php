<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;
class ProfileController extends Controller
{
    #[OA\Get(
        path: "/api/perfil",
        summary: "Obtener perfil del usuario",
        description: "Devuelve los datos del usuario actualmente logueado. Requiere token JWT.",
        tags: ["Perfil"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Datos del perfil obtenidos")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    public function show()
    {
        try {
            $userId = JWTAuth::user()->id;
            $user = User::find($userId);

            if (!$user) {
                return $this->errorResponse('Perfil no encontrado.', 404);
            }

            return $this->successResponse(
                ['user' => $user],
                'Perfil obtenido correctamente.'
            );
        } catch (\Exception $e) {
            Log::error('Error al obtener el perfil', ['exception' => $e]);

            return $this->errorResponse('Error al obtener el perfil.', 500);
        }
    }

    #[OA\Put(
        path: "/api/perfil",
        summary: "Actualizar perfil del usuario",
        description: "Actualiza los datos del usuario autenticado. Requiere token JWT. Para cambiar la contraseña hay que enviar también `current_password`; en ese caso la respuesta incluye un token nuevo en `data.token` y el anterior deja de ser válido.",
        tags: ["Perfil"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Prueba234"),
                new OA\Property(property: "surname", type: "string", example: "Prueba"),
                new OA\Property(property: "username", type: "string", example: "prueba3424"),
                new OA\Property(property: "email", type: "string", format: "email", example: "prueba@ejemplo.com"),
                new OA\Property(property: "current_password", type: "string", format: "password", description: "Obligatoria si se envía `password`"),
                new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "prueba244"),
                new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "prueba244")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Perfil actualizado correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 422, description: "Error de validación")]
    #[OA\Response(response: 500, description: "Error al actualizar el perfil")]
    public function update(Request $request)
    {
        try {
            $authUser = JWTAuth::user();

            if (!$authUser) {
                return $this->errorResponse('Usuario no autenticado.', 401);
            }

            $user = User::find($authUser->id);

            if (!$user) {
                return $this->errorResponse('Perfil no encontrado.', 404);
            }

            $reglas = [
                'name' => 'sometimes|string|max:255',
                'surname' => 'sometimes|string|max:255',
                'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            ];

            if ($request->filled('password')) {
                // Cambiar la contraseña exige conocer la actual: un token robado no basta
                $reglas['current_password'] = 'required|string';
                $reglas['password'] = 'string|min:8|confirmed|different:current_password';
            }

            $validator = Validator::make($request->all(), $reglas, [
                'username.unique' => 'Ese nombre de usuario ya está en uso.',
                'email.unique' => 'El email ya está registrado.',
                'current_password.required' => 'Debes indicar tu contraseña actual.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.different' => 'La nueva contraseña debe ser distinta de la actual.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            if ($request->filled('password') && ! Hash::check($request->current_password, $user->password)) {
                return $this->validationErrorResponse([
                    'current_password' => ['La contraseña actual no es correcta.'],
                ]);
            }

            foreach (['name', 'surname', 'username', 'email'] as $field) {
                if ($request->filled($field)) {
                    $user->$field = $request->$field;
                }
            }
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $data = ['user' => $user];

            // Tras cambiar la contraseña, el token usado deja de valer y se entrega uno nuevo
            if ($request->filled('password')) {
                $data['token'] = JWTAuth::fromUser($user);
                JWTAuth::invalidate(JWTAuth::getToken());
            }

            return $this->successResponse($data, 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el perfil', ['exception' => $e]);

            return $this->errorResponse('Error al actualizar el perfil.', 500);
        }
    }
}
