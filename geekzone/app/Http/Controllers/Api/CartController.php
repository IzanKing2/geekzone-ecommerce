<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    /** Máximo de unidades de un mismo producto en el carrito. */
    private const MAX_QUANTITY = 99;

    #[OA\Get(
        path: '/api/carrito',
        summary: 'Ver carrito del usuario',
        description: 'Devuelve todos los items del carrito del usuario autenticado. Requiere token JWT',
        tags: ['Carrito'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'Carrito obtenido correctamente',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', nullable: true, example: null),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'cart', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'total', type: 'number', example: 59.99),
                        new OA\Property(property: 'items_count', type: 'integer', example: 3),
                    ]
                ),
                new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    public function index()
    {
        $user = Auth::user();

        // Obtener items del carrito con la info del producto.
        // whereHas('product') excluye los productos eliminados (soft delete).
        $items = Cart::with('product.category')
            ->whereHas('product')
            ->where('user_id', $user->id)
            ->get();

        // Calcular el total del carrito
        $total = 0;
        foreach ($items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return $this->successResponse([
            'cart' => $items,
            'total' => round($total, 2),
            'items_count' => $items->count(),
        ]);
    }

    #[OA\Post(
        path: '/api/carrito',
        summary: 'Añadir producto al carrito',
        description: 'Añade un producto al carrito del usuario. Requiere token JWT',
        tags: ['Carrito'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['product_id'],
            properties: [
                new OA\Property(property: 'product_id', type: 'integer', example: 1),
                new OA\Property(property: 'quantity', type: 'integer', example: 2),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Producto añadido al carrito correctamente')]
    #[OA\Response(response: 400, description: 'Stock insuficiente (se compara con la cantidad total que habría en el carrito) o cantidad máxima superada (99)')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 404, description: 'El producto ya no está disponible')]
    #[OA\Response(response: 422, description: 'Error de validación')]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'quantity' => 'integer|min:1|max:'.self::MAX_QUANTITY,
        ], [
            'product_id.required' => 'El producto es requerido.',
            'product_id.exists' => 'El producto no existe.',
            'quantity.min' => 'La cantidad debe ser mayor a 0.',
            'quantity.max' => 'La cantidad máxima por producto es '.self::MAX_QUANTITY.'.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = Auth::user();
        $quantity = $request->input('quantity', 1); // Por defecto, 1 item

        // Bloqueamos la fila del producto: dos peticiones simultáneas para el mismo
        // producto se serializan y no pueden duplicar el ítem ni saltarse el stock.
        $resultado = DB::transaction(function () use ($user, $quantity, $request) {
            $product = Product::lockForUpdate()->find($request->product_id);

            if (! $product) {
                return ['error' => 'El producto ya no está disponible.', 'status' => 404];
            }

            $item = Cart::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            // El stock se compara con lo que habría en total en el carrito, no solo con lo que se añade
            $total = ($item ? $item->quantity : 0) + $quantity;

            if ($product->stock < $total) {
                return [
                    'error' => 'No hay suficiente stock. Disponible: '.$product->stock,
                    'status' => 400,
                ];
            }

            if ($total > self::MAX_QUANTITY) {
                return [
                    'error' => 'La cantidad máxima por producto es '.self::MAX_QUANTITY.'.',
                    'status' => 400,
                ];
            }

            if ($item) {
                $item->quantity = $total;
                $item->save();
            } else {
                $item = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ]);
            }

            return ['item' => $item];
        });

        if (isset($resultado['error'])) {
            return $this->errorResponse($resultado['error'], $resultado['status']);
        }

        $item = $resultado['item'];
        $item->load('product');

        return $this->successResponse(['cart_item' => $item], 'Producto agregado al carrito correctamente.', 201);
    }

    #[OA\Put(
        path: '/api/carrito/{id}',
        summary: 'Actualizar cantidad de un item del carrito',
        description: 'Modifica la cantidad de un item en el carrito. Requiere token JWT',
        tags: ['Carrito'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID del item',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['quantity'],
            properties: [
                new OA\Property(property: 'quantity', type: 'integer', example: 3),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Cantidad actualizada correctamente')]
    #[OA\Response(response: 400, description: 'Stock insuficiente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 404, description: 'Item no encontrado en el carrito')]
    #[OA\Response(response: 422, description: 'Error de validación')]
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:'.self::MAX_QUANTITY,
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = Auth::user();

        $item = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $item) {
            return $this->errorResponse('Item no encontrado en el carrito.', 404);
        }

        $product = Product::find($item->product_id);

        // El producto se eliminó del catálogo después de añadirlo: se quita del carrito
        if (! $product) {
            $item->delete();

            return $this->errorResponse('El producto ya no está disponible y se ha quitado del carrito.', 404);
        }

        if ($product->stock < $request->quantity) {
            return $this->errorResponse('No hay suficiente stock. Disponible: '.$product->stock, 400);
        }

        $item->quantity = $request->quantity;
        $item->save();
        $item->load('product');

        return $this->successResponse(['cart_item' => $item], 'Cantidad actualizada correctamente.');
    }

    #[OA\Delete(
        path: '/api/carrito/{id}',
        summary: 'Eliminar item del carrito',
        description: 'Elimina un producto del carrito del usuario. Requiere token JWT',
        tags: ['Carrito'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID del item',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(response: 200, description: 'Item eliminado del carrito correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 404, description: 'Item no encontrado en el carrito')]
    public function destroy($id)
    {
        $user = Auth::user();

        $item = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $item) {
            return $this->errorResponse('Item no encontrado en el carrito.', 404);
        }

        $item->delete();

        return $this->successResponse(null, 'Item eliminado del carrito correctamente.');
    }
}
