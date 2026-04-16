<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController
{
    public function index()
    {
        return view('shop', compact('products', 'categories', 'activeCategory'));
    }
}
