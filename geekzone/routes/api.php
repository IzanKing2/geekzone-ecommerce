<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;

// ———————————————————————————————————————————————————————————————————————————
// RUTAS PÚBLICAS (sin autenticación)
// ———————————————————————————————————————————————————————————————————————————

// ———— Registro de usuario (límite anti fuerza bruta / abuso) —————————————————————————————————
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

// ———— Productos y categorías: acceso público —————————————————————————————————
Route::get('/categorias', [CategoryController::class, 'index']);
Route::get('/productos', [ProductController::class, 'index']);
Route::get('/productos/{id}', [ProductController::class, 'show']);

// ———————————————————————————————————————————————————————————————————————————
// RUTAS PROTEGIDAS (requieren JWT — cualquier usuario)
// ———————————————————————————————————————————————————————————————————————————

Route::middleware(JwtMiddleware::class)->group(function () {
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

    // ———— Favoritos —————————————————————————————————
    Route::get('/favoritos', [FavoriteController::class, 'index']);
    Route::post('/favoritos', [FavoriteController::class, 'store']);
    Route::delete('/favoritos/{productId}', [FavoriteController::class, 'destroy']);

    // ———— Subir imagen —————————————————————————————————
    // Acepta archvos via multipart/form-data (no JSON)
    Route::post('/imagenes', [ImageController::class, 'store']);
});

// ———————————————————————————————————————————————————————————————————————————
// RUTAS DE ADMINISTRACIÓN (requieren JWT + rol admin)
// ———————————————————————————————————————————————————————————————————————————

Route::middleware([JwtMiddleware::class, 'admin'])->group(function () {

    // ———— Dashboard —————————————————————————————————
    Route::get('/admin/dashboard/resumen', [AdminDashboardController::class, 'resumen']);
    Route::get('/admin/dashboard/ingresos', [AdminDashboardController::class, 'ingresos']);
    Route::get('/admin/dashboard/top-productos', [AdminDashboardController::class, 'topProductos']);
    Route::get('/admin/dashboard/pedidos-por-cliente', [AdminDashboardController::class, 'pedidosPorCliente']);

    // ———— CRUD Categorías —————————————————————————————————
    Route::post('/categorias', [CategoryController::class, 'store']);
    Route::put('/categorias/{id}', [CategoryController::class, 'update']);
    Route::delete('/categorias/{id}', [CategoryController::class, 'destroy']);

    // ———— CRUD Productos —————————————————————————————————
    Route::post('/productos', [ProductController::class, 'store']);
    Route::put('/productos/{id}', [ProductController::class, 'update']);
    Route::delete('/productos/{id}', [ProductController::class, 'destroy']);
});
