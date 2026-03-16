<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    public function index()
    {
        try {
            $user = JWTAuth::user();

            $orders = Order::with('details.product')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse([
                'orders' => $orders,
                'total'  => $orders->count(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los pedidos.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    public function store()
    {
        try {
            $user = JWTAuth::user();

            $cartItems = Cart::with('product')
                ->where('user_id', $user->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return $this->errorResponse(
                    'No hay items en el carrito. Añade productos para crear un pedido.',
                    404
                );
            }

            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    return $this->errorResponse(
                        'No hay suficiente stock para el producto ' . $item->product->name . '. Disponible: ' . $item->product->stock,
                        400
                    );
                }
            }

            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item->product->price * $item->quantity;
            }

            $order = DB::transaction(function () use ($user, $cartItems, $total) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'status'  => 'pendiente',
                    'total'   => round($total, 2),
                ]);

                foreach ($cartItems as $item) {
                    OrderDetail::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price,
                    ]);

                    $product = Product::lockForUpdate()->find($item->product_id);

                    if (!$product) {
                        throw new \RuntimeException('Producto no encontrado.');
                    }

                    if ($product->stock < $item->quantity) {
                        throw new \RuntimeException(
                            'No hay suficiente stock para el producto ' . $product->name . '. Disponible: ' . $product->stock
                        );
                    }

                    $product->stock -= $item->quantity;
                    $product->save();
                }

                Cart::where('user_id', $user->id)->delete();

                return $order;
            });

            $order->load('details.product');

            return $this->successResponse(
                ['order' => $order],
                'Pedido creado correctamente.',
                201
            );
        } catch (\RuntimeException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                400
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear el pedido.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
