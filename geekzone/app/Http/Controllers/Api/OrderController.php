<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $orders = Order::with('details.product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders,
            'total' => $orders->count(),
        ], 200);
    }

    public function store()
    {
        $user = Auth::user();

        $CartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($CartItems->isEmpty()) {
            return response()->json([
                'message' => 'No hay items en el carrito. Añade productos para crear un pedido.',
            ], 404);
        }

        foreach ($CartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => 'No hay suficiente stock para el producto ' . $item->product->name . '. Disponible: ' . $item->product->stock,
                ], 400);
            }
        }

        $total = 0;
        foreach ($CartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pendiente',
                'total' => round($total, 2),
        ]);

        $orderDetails = [];
        foreach ($CartItems as $item) {
            OrderDetail::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->product->price,
            ]);

            $item->product->decrement('stock', $item->quantity);
        }

        Cart::where('user_id', $user->id)
            ->delete();

        $order->load('details.product');

        return response()->json([
            'message' => 'Pedido creado correctamente.',
            'order' => $order,
        ], 201);
    }
}
