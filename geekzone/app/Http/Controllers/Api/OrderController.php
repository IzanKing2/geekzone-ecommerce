<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            Log::error('Error al obtener los pedidos', ['exception' => $e]);

            return $this->errorResponse('Error al obtener los pedidos.', 500);
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

            // Todo ocurre dentro de la transacción: leer el carrito, comprobar stock,
            // calcular precios y total, descontar stock y vaciar el carrito.
            // Así un doble clic o dos peticiones simultáneas no pueden crear dos pedidos
            // ni vender stock que no existe.
            $resultado = DB::transaction(function () use ($user) {
                // Mutex por usuario: serializa los pedidos del mismo usuario
                User::lockForUpdate()->find($user->id);

                $cartItems = Cart::where('user_id', $user->id)->orderBy('product_id')->get();

                // Productos bloqueados en orden de id (evita interbloqueos entre pedidos).
                // Los productos eliminados (soft delete) no aparecen aquí.
                $products = Product::whereIn('id', $cartItems->pluck('product_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Ítems cuyo producto ya no existe: se quitan del carrito, el usuario no los ve
                $huerfanos = $cartItems->reject(fn ($item) => $products->has($item->product_id));
                if ($huerfanos->isNotEmpty()) {
                    Cart::whereIn('id', $huerfanos->pluck('id'))->delete();
                    $cartItems = $cartItems->reject(fn ($item) => $huerfanos->contains('id', $item->id));
                }

                if ($cartItems->isEmpty()) {
                    return ['error' => 'No hay items en el carrito. Añade productos para crear un pedido.', 'status' => 404];
                }

                // Total en céntimos (enteros): sin errores de redondeo de decimales
                $totalCentimos = 0;
                foreach ($cartItems as $item) {
                    $product = $products[$item->product_id];

                    if ($product->stock < $item->quantity) {
                        throw new \RuntimeException(
                            'No hay suficiente stock para el producto ' . $product->name . '. Disponible: ' . $product->stock
                        );
                    }

                    $totalCentimos += (int) round(((float) $product->price) * 100) * $item->quantity;
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'pendiente',
                    'total' => $totalCentimos / 100,
                ]);

                foreach ($cartItems as $item) {
                    $product = $products[$item->product_id];

                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'price' => $product->price,
                    ]);

                    $product->stock -= $item->quantity;
                    $product->save();
                }

                Cart::where('user_id', $user->id)->delete();

                return ['order' => $order];
            });

            if (isset($resultado['error'])) {
                return $this->errorResponse($resultado['error'], $resultado['status']);
            }

            $order = $resultado['order'];
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
            Log::error('Error al crear el pedido', ['exception' => $e]);

            return $this->errorResponse('Error al crear el pedido.', 500);
        }
    }
}
