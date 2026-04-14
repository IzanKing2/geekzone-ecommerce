<?php

namespace App\Http\Controllers;

class AuthController
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }
}
