<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/shop', function() {
    return view('shop');
})->name('shop');
