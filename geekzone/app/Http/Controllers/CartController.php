<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    public function index()
    {
        try {
            $user = JWTAuth::user();

            // Obtener items del carrito con la info del producto
            $items = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();
            
            // Calcular el total del carrito
            $total = 0;
            foreach ($items as $item) {
                $total += $item->product->price * $item->quantity;
            }

            return $this->successResponse([
                'cart'        => $items,
                'total'       => round($total, 2),
                'items_count' => $items->count(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los items del carrito.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'integer|min:1',
            ], [
                'product_id.required' => 'El producto es requerido.',
                'product_id.exists' => 'El producto no existe.',
                'quantity.min' => 'La cantidad debe ser mayor a 0.',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = JWTAuth::user();
            $quantity = $request->input('quantity', 1); // Por defecto, 1 item

            // Varificar que hay suficiente stock
            $product = Product::find($request->product_id);

            if ($product->stock < $quantity) {
                return $this->errorResponse(
                    'No hay suficiente stock. Disponible: ' . $product->stock,
                    400
                );
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

            return $this->successResponse(
                ['cart_item' => $item],
                'Producto agregado al carrito correctamente.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al agregar el producto al carrito.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = JWTAuth::user();

            $item = Cart::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$item) {
                return $this->errorResponse('Item no encontrado en el carrito.', 404);
            }

            $product = Product::find($item->product_id);

            if ($product->stock < $request->quantity) {
                return $this->errorResponse(
                    'No hay suficiente stock. Disponible: ' . $product->stock,
                    400
                );
            }

            $item->quantity = $request->quantity;
            $item->save();
            $item->load('product');

            return $this->successResponse(
                ['cart_item' => $item],
                'Cantidad actualizada correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar la cantidad del producto.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    public function destroy($id)
    {
        try {
            $user = JWTAuth::user();
            
            $item = Cart::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$item) {
                return $this->errorResponse('Item no encontrado en el carrito.', 404);
            }
            
            $item->delete();
        
            return $this->successResponse(
                null,
                'Item eliminado del carrito correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar el item del carrito.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
