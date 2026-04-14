<?php

use App\Http\Controllers\AplicationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AplicationController::class, 'index'])->name('shop');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
