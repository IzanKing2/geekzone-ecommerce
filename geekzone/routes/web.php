<?php

use App\Http\Controllers\AplicationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AplicationController::class, 'index'])->name('shop');
Route::get('/catalogo', [AplicationController::class, 'catalog'])->name('catalog');
Route::get('/producto/{id}', [AplicationController::class, 'show'])->name('product.show');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

Route::get('/cart', function() {
    return view('cart.cart');
})->name('cart');

Route::get('/panel', function() {
    return view('userPanel');
})->name('panel');

Route::get('/favoritos', function() {
    return view('favorites');
})->name('favorites');
