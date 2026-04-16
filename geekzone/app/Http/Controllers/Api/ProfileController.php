<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
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
            return $this->errorResponse(
                'Error al obtener el perfil.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

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
                'name'     => 'sometimes|string|max:255',
                'surname'  => 'sometimes|string|max:255',
                'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
                'email'    => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            ];

            if ($request->filled('password')) {
                $reglas['password'] = 'string|min:6|confirmed';
            }

            $validator = Validator::make($request->all(), $reglas, [
                'username.unique'    => 'Ese nombre de usuario ya está en uso.',
                'email.unique'       => 'El email ya está registrado.',
                'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
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

            return $this->successResponse(
                ['user' => $user],
                'Perfil actualizado correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar el perfil.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
