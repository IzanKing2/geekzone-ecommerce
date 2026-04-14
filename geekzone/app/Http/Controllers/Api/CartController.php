<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
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
