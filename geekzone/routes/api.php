<?php

use App\Http\Controllers\AuthController;
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
    Route::post('/logout', [AuthController::class, 'logout']);
});