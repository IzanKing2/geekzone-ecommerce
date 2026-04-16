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

        $query = Product::with('category')->where('featured', true);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->paginate(12);

        $activeCategory = $request->category;

        return view('shop', compact('products', 'categories', 'activeCategory'));
    }

    public function catalog(Request $request)
    {
        $categories = Category::withCount('products')->get();

        $query = Product::with('category');

        if ($request->filled('categories')) {
            $query->whereIn('category_id', (array) $request->categories);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        $products = $query->paginate(12);

        $activeCategories = (array) $request->categories;

        return view('catalog', compact('products', 'categories', 'activeCategories'));
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        $related = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('product', compact('product', 'related'));
    }
}
