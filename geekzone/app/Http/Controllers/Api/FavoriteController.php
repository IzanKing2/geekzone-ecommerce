<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class FavoriteController extends Controller
{
    #[OA\Get(
        path: "/api/favoritos",
        summary: "Ver favoritos del usuario",
        description: "Devuelve todos los productos marcados como favoritos por el usuario. Requiere token JWT.",
        tags: ["Favoritos"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Favoritos obtenidos correctamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "favorites", type: "array", items: new OA\Items(type: "object")),
                new OA\Property(property: "count", type: "integer", example: 5)
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
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

    #[OA\Post(
        path: "/api/favoritos",
        summary: "Añadir producto a favoritos",
        description: "Marca un producto como favorito. Requiere token JWT.",
        tags: ["Favoritos"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id"],
            properties: [
                new OA\Property(property: "product_id", type: "integer", example: 1)
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Producto añadido a favoritos correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 409, description: "El producto ya está en favoritos")]
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

    #[OA\Delete(
        path: "/api/favoritos/{productId}",
        summary: "Eliminar producto de favoritos",
        description: "Elimina un producto de los favoritos del usuario. Requiere token JWT",
        tags: ["Favoritos"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "productId",
        in: "path",
        required: true,
        description: "ID del producto",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 201, description: "Producto eliminado de favoritos correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 404, description: "Favorito no encontrado")]
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
