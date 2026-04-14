<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class AplicationController
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->get();

        $activeCategory = $request->category;

        return view('shop', compact('products', 'categories', 'activeCategory'));
    }
}
