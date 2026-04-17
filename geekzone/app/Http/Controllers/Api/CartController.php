<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    #[OA\Get(
        path: "/api/carrito",
        summary: "Ver carrito del usuario",
        description: "Devuelve todos los items del carrito del usuario autenticado. Requiere token JWT",
        tags: ["Carrito"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Carrito obtenido correctamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "cart", type: "array", items: new OA\Items(type: "object")),
                new OA\Property(property: "total", type: "number", example: 59.99),
                new OA\Property(property: "items_count", type: "integer", example: 3)
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    public function index()
    {
        $user = Auth::user();

        // Obtener items del carrito con la info del producto
        $items = Cart::with('product.category')
            ->where('user_id', $user->id)
            ->get();

        // Calcular el total del carrito
        $total = 0;
        foreach ($items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return response()->json([
            'cart' => $items,
            'total' => round($total, 2),
            'items_count' => $items->count(),
        ], 200);
    }

    #[OA\Post(
        path: "/api/carrito",
        summary: "Añadir producto al carrito",
        description: "Añade un producto al carrito del usuario. Requiere token JWT",
        tags: ["Carrito"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id"],
            properties: [
                new OA\Property(property: "product_id", type: "integer", example: 1),
                new OA\Property(property: "quantity", type: "integer", example: 2)
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Producto añadido al carrito correctamente")]
    #[OA\Response(response: 400, description: "Stock insuficiente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1',
        ], [
            'product_id.required' => 'El producto es requerido.',
            'product_id.exists' => 'El producto no existe.',
            'quantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $quantity = $request->input('quantity', 1); // Por defecto, 1 item

        // Varificar que hay suficiente stock
        $product = Product::find($request->product_id);

        if ($product->stock < $quantity) {
            return response()->json([
                'message' => 'No hay suficiente stock. Disponible: ' . $product->stock,
            ], 400);
        }

        $item = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            $item = Cart::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => $quantity,
            ]);
        }

        $item->load('product');

        return response()->json([
            'message' => 'Producto agregado al carrito correctamente.',
            'cart_item' => $item,
        ], 201);
    }

    #[OA\Put(
        path: "/api/carrito/{id}",
        summary: "Actualizar cantidad de un item del carrito",
        description: "Modifica la cantidad de un item en el carrito. Requiere token JWT",
        tags: ["Carrito"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID del item",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["quantity"],
            properties: [
                new OA\Property(property: "quantity", type: "integer", example: 3)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Cantidad actualizada correctamente")]
    #[OA\Response(response: 400, description: "Stock insuficiente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 404, description: "Item no encontrado en el carrito")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        $item = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item no encontrado en el carrito.',
            ], 404);
        }

        $product = Product::find($item->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'No hay suficiente stock. Disponible: ' . $product->stock,
            ], 400);
        }

        $item->quantity = $request->quantity;
        $item->save();
        $item->load('product');

        return response()->json([
            'message' => 'Cantidad actualizada correctamente.',
            'cart_item' => $item,
        ], 200);
    }

    #[OA\Delete(
        path: "/api/carrito/{id}",
        summary: "Eliminar item del carrito",
        description: "Elimina un producto del carrito del usuario. Requiere token JWT",
        tags: ["Carrito"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID del item",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 200, description: "Item eliminado del carrito correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 404, description: "Item no encontrado en el carrito")]
    public function destroy($id)
    {
        $user = Auth::user();

        $item = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Item no encontrado en el carrito.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item eliminado del carrito correctamente.',
        ], 200);
    }
}
