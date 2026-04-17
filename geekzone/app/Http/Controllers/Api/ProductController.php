<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: "/api/productos",
        summary: "Obtener lista de productos",
        description: "Devuelve todos los productos del catálogo",
        tags: ["Productos"]
    )]
    #[OA\Response(response: 200, description: "Lista de productos devuelta correctamente")]

    public function index(Request $request)
    {
        try {
            $query = Product::with('category');

            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->input('search') . '%');
            }

            $perPage = (int) $request->input('per_page', 12);
            $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 12;

            $products = $query
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->successResponse([
                'items' => $products->items(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los productos.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    #[OA\Get(
        path: "/api/productos/{id}",
        summary: "Ver detalles de un producto",
        tags: ["Productos"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID del producto",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 200, description: "Detalles del producto")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    #[OA\Response(response: 500, description: "Error al obtener el producto")]
    public function show(int $id)
    {
        try {
            $product = Product::with('category')->find($id);

            if (!$product) {
                return $this->errorResponse('Producto no encontrado.', 404);
            }

            return $this->successResponse(['product' => $product]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener el producto.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS DE ADMINISTRACIÓN (protegidos con middleware admin)
    // ——————————————————————————————————————————————————————————————————————————

    #[OA\Post(
        path: "/api/productos",
        summary: "Crear producto (Administrador)",
        description: "Crea un nuevo producto en el catálogo. Requiere token JWT con rol de administrador.",
        tags: ["Productos (Administrador)"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "price", "stock", "category_id"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Producto de prueba"),
                new OA\Property(property: "description", type: "string", example: "Producto ejemplo"),
                new OA\Property(property: "price", type: "number", format: "float", example: 79.99),
                new OA\Property(property: "stock", type: "integer", example: 50),
                new OA\Property(property: "image_url", type: "string", example: "https://ejemplo.com/ejemplo.jpg"),
                new OA\Property(property: "category_id", type: "integer", example: 1)
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Producto creado correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 403, description: "No tienes permisos de administrador")]
    #[OA\Response(response: 422, description: "Error de validación")]
    #[OA\Response(response: 500, description: "Error al crear el producto")]
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0.01',
                'stock' => 'required|integer|min:0',
                'image_url' => 'nullable|string|max:500',
                'category_id' => 'required|integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $product = Product::create($request->all());
            $product->load('category');

            return $this->successResponse(
                ['product' => $product],
                'Producto creado correctamente.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear el producto.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    #[OA\Put(
        path: "/api/productos/{id}",
        summary: "Actualizar producto (Administrador)",
        description: "Actualiza los datos de un producto existente. Requiere token JWT con rol de administrador.",
        tags: ["Productos (Administrador)"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID del producto",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Producto actualizado"),
                new OA\Property(property: "description", type: "string", example: "Producto actualizado"),
                new OA\Property(property: "price", type: "number", format: "float", example: 89.99),
                new OA\Property(property: "stock", type: "integer", example: 30),
                new OA\Property(property: "image_url", type: "string", example: "https://ejemplo.com/actualizado.jpg"),
                new OA\Property(property: "category_id", type: "integer", example: 2)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Producto actualizado correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 403, description: "No tienes permisos de administrador")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function update(Request $request, int $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->errorResponse('Producto no encontrado.', 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'price' => 'sometimes|numeric|min:0.01',
                'stock' => 'sometimes|integer|min:0',
                'image_url' => 'sometimes|nullable|string|max:500',
                'category_id' => 'sometimes|integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $product->update($request->all());
            $product->load('category');

            return $this->successResponse(
                ['product' => $product],
                'Producto actualizado correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar el producto.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    #[OA\Delete(
        path: "/api/productos/{id}",
        summary: "Eliminar producto (Administrador)",
        description: "Elimina un producto del catálogo. Requiere token JWT con rol de administrador.",
        tags: ["Productos (Administrador)"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID del producto",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 200, description: "Producto eliminado correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 403, description: "No tienes permisos de administrador")]
    #[OA\Response(response: 404, description: "Producto no encontrado")]
    #[OA\Response(response: 500, description: "Error al eliminar el producto")]
    public function destroy(int $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->errorResponse('Producto no encontrado.', 404);
            }

            $product->delete();

            return $this->successResponse(
                null,
                'Producto eliminado correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar el producto.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
