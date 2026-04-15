<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with('product.category')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'favorites' => $favorites,
            'count' => $favorites->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ], [
            'product_id.required' => 'El producto es requerido.',
            'product_id.exists' => 'El producto no existe.',
        ]);

        $exists = Favorite::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'El producto ya está en favoritos.'], 409);
        }

        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        $favorite->load('product.category');

        return response()->json([
            'message' => 'Producto añadido a favoritos.',
            'favorite' => $favorite,
        ], 201);
    }

    public function destroy($productId)
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Favorito no encontrado.'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Producto eliminado de favoritos.']);
    }
}
