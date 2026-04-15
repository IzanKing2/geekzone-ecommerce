<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show()
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'Perfil no encontrado.',
            ], 404);
        }

        return response()->json([
            'message' => 'Perfil obtenido correctamente.',
            'user' => $user,
        ], 200);
    }

    public function update(Request $request)
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json([
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $user = User::find($authUser->id);

        if (!$user) {
            return response()->json([
                'message' => 'Perfil no encontrado.',
            ], 404);
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
            'username.unique' => 'Ese nombre de usuario ya está en uso.',
            'email.unique'    => 'El email ya está registrado.',
            'password.min'    => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
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

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user' => $user,
        ], 200);
    }
}
