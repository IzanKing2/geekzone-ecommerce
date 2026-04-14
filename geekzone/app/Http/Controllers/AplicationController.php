<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class AplicationController
{
    public function index()
    {
        $products = Product::all();
        $categories = Category::all();

        return view('shop', compact('products', 'categories'));
    }
}
