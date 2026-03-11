<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ———————————————————————————————————————————————————————————————————————————
// RUTAS PÚBLICAS (sin autenticación)
// ———————————————————————————————————————————————————————————————————————————

// ———— Registro de usuario —————————————————————————————————
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ———————————————————————————————————————————————————————————————————————————
// RUTAS PROTEGIDAS (requieren JWT — cualquier usuario)
// ———————————————————————————————————————————————————————————————————————————

Route::middleware('jwt.auth')->group(function () {
    // ———— Registro de usuario —————————————————————————————————
    Route::post('/logout', [AuthController::class, 'logout']);
    // ———— Perfil de usuario —————————————————————————————————
    Route::get('/perfil', [ProfileController::class, 'show']);
    Route::put('/perfil', [ProfileController::class, 'update']);
});