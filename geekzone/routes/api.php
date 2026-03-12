<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ———————————————————————————————————————————————————————————————————————————
// RUTAS PÚBLICAS (sin autenticación)
// ———————————————————————————————————————————————————————————————————————————

// ———— Registro de usuario —————————————————————————————————
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ———— Productos y categorías: acceso público —————————————————————————————————
Route::get('/categorias', [CategoryController::class, 'index']);
Route::get('/productos', [ProductController::class, 'index']);
Route::get('/productos/{id}', [ProductController::class, 'show']);

// ———————————————————————————————————————————————————————————————————————————
// RUTAS PROTEGIDAS (requieren JWT — cualquier usuario)
// ———————————————————————————————————————————————————————————————————————————

Route::middleware('jwt.auth')->group(function () {
    // ———— Registro de usuario —————————————————————————————————
    Route::post('/logout', [AuthController::class, 'logout']);

    // ———— Perfil de usuario —————————————————————————————————
    Route::get('/perfil', [ProfileController::class, 'show']);
    Route::put('/perfil', [ProfileController::class, 'update']);

    // ———— CRUD Carrito —————————————————————————————————
    Route::get('/carrito', [CartController::class, 'index']);
    Route::post('/carrito', [CartController::class, 'store']);
    Route::put('/carrito/{id}', [CartController::class, 'update']);
    Route::delete('/carrito/{id}', [CartController::class, 'destroy']);

    // ———— Pedidos —————————————————————————————————
    Route::get('/pedidos', [OrderController::class, 'index']);
    Route::post('/pedidos', [OrderController::class, 'store']);

    // ———— Subir imagen —————————————————————————————————
    // Acepta archvos via multipart/form-data (no JSON)
    Route::post('/imagenes', [ImageController::class, 'store']);
});

// ———————————————————————————————————————————————————————————————————————————
// RUTAS DE ADMINISTRACIÓN (requieren JWT + rol admin)
// ———————————————————————————————————————————————————————————————————————————

Route::middleware(['jwt.auth', 'admin'])->group(function () {
    // ———— CRUD Categorías —————————————————————————————————
    Route::post('/categorias', [CategoryController::class, 'store']);
    Route::put('/categorias/{id}', [CategoryController::class, 'update']);
    Route::delete('/categorias/{id}', [CategoryController::class, 'destroy']);

    // ———— CRUD Productos —————————————————————————————————
    Route::post('/productos', [ProductController::class, 'store']);
    Route::put('/productos/{id}', [ProductController::class, 'update']);
    Route::delete('/productos/{id}', [ProductController::class, 'destroy']);
});