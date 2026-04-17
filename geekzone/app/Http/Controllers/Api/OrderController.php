<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        path: "/api/pedidos",
        summary: "Ver pedidos del usuario",
        description: "Devuelve todos los pedidos realizados por el usuario autenticado. Requiere token JWT.",
        tags: ["Pedidos"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Pedidos obtenidos correctamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "orders", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "total", type: "integer", example: 3)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
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
                'total' => $orders->count(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los pedidos.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    #[OA\Post(
        path: "/api/pedidos",
        summary: "Crear pedido desde el carrito",
        description: "Crea un pedido con los productos del carrito. Vacía el carrito automáticamente. Requiere token JWT",
        tags: ["Pedidos"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 201,
        description: "Pedido creado correctamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Pedido creado correctamente."),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "order", type: "object")
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Stock insuficiente o carrito vacío")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 404, description: "No hay items en el carrito")]
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
                    'status' => 'pendiente',
                    'total' => round($total, 2),
                ]);

                foreach ($cartItems as $item) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
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
