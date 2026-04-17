<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[OA\Get(
        path: "/api/categorias",
        summary: "Obtener lista de categorías",
        description: "Devuelve todas las categorías disponibles",
        tags: ["Categorías"]
    )]
    #[OA\Response(response: 200, description: "Lista de categorías devuelta")]
    public function index()
    {
        try {
            $categories = DB::select('
                SELECT
                    c.id,
                    c.name,
                    c.description,
                    c.image_url,
                    c.created_at,
                    c.updated_at,
                    COUNT(p.id) AS products_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                GROUP BY c.id, c.name, c.description, c.image_url, c.created_at, c.updated_at
                ORDER BY c.name ASC
            ');

            return $this->successResponse([
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener las categorías.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS DE ADMINISTRACIÓN
    // ——————————————————————————————————————————————————————————————————————————

    #[OA\Post(
        path: "/api/categorias",
        summary: "Crear una nueva categoría",
        description: "Crea una nueva categoría. Requiere token JWT con rol de administrador",
        tags: ["Categorías (Administrador)"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Categoria ejemplo"),
                new OA\Property(property: "description", type: "string", example: "Descripcion categoria ejemplo"),
                new OA\Property(property: "image_url", type: "string", example: "https://categoria.com/imagen.jpg")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Categoría creada exitosamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 403, description: "Error. No tiene permisos de administrador")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Verificar unicidad del nombre con SQL puro
        $exists = DB::select(
            'SELECT id FROM categories WHERE name = ? LIMIT 1',
            [$request->name]
        );

        if (!empty($exists)) {
            return $this->validationErrorResponse(
                ['name' => ['Ya existe una categoría con ese nombre.']]
            );
        }

        $now = now()->toDateTimeString();

        DB::insert(
            'INSERT INTO categories (name, description, image_url, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?)',
            [
                $request->name,
                $request->description,
                $request->image_url,
                $now,
                $now,
            ]
        );

        $newId = DB::getPdo()->lastInsertId();

        $category = DB::select(
            'SELECT * FROM categories WHERE id = ?',
            [$newId]
        );

        return $this->successResponse(
            ['category' => $category[0]],
            'Categoría creada correctamente.',
            201
        );
    }

    #[OA\Put(
        path: "/api/categorias/{id}",
        summary: "Actualizar categoría",
        description: "Actualiza los datos de una categoría existente. Requiere token JWT con rol de administrador",
        tags: ["Categorías (Administrador)"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID de la categoría",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Ejemplo Nombre"),
                new OA\Property(property: "description", type: "string", example: "Ejemplo Descripcion"),
                new OA\Property(property: "image_url", type: "string", example: "https://foto.com/foto.jpg")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Categoría actualizada correctamente")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    public function update(Request $request, int $id)
    {
        try {
            $category = DB::select(
                'SELECT * FROM categories WHERE id = ? LIMIT 1',
                [$id]
            );

            if (empty($category)) {
                return $this->errorResponse('Categoría no encontrada.', 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'image_url' => 'sometimes|nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Verificar unicidad del nombre excluyendo el registro actual
            if ($request->has('name')) {
                $duplicate = DB::select(
                    'SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1',
                    [$request->name, $id]
                );

                if (!empty($duplicate)) {
                    return $this->validationErrorResponse(
                        ['name' => ['Ya existe una categoría con ese nombre.']]
                    );
                }
            }

            $current = $category[0];

            DB::update(
                'UPDATE categories
                 SET name = ?, description = ?, image_url = ?, updated_at = ?
                 WHERE id = ?',
                [
                    $request->input('name', $current->name),
                    $request->input('description', $current->description),
                    $request->input('image_url', $current->image_url),
                    now()->toDateTimeString(),
                    $id,
                ]
            );

            $updated = DB::select(
                'SELECT * FROM categories WHERE id = ? LIMIT 1',
                [$id]
            );

            return $this->successResponse(
                ['category' => $updated[0]],
                'Categoría actualizada correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar la categoría.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

    #[OA\Delete(
        path: "/api/categorias/{id}",
        summary: "Eliminar categoría (Admin)",
        description: "Elimina una categoría. No se puede eliminar si tiene productos asociados. Requiere token JWT con rol de administrador.",
        tags: ["Categorías (Administrador)"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID de la categoría",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 200, description: "Categoría eliminada correctamente")]
    #[OA\Response(response: 400, description: "No se puede eliminar: tiene productos asociados")]
    #[OA\Response(response: 401, description: "No autorizado. Token inválido o no existe")]
    #[OA\Response(response: 403, description: "No tienes permisos de administrador")]
    #[OA\Response(response: 404, description: "Categoría no encontrada")]
    public function destroy(int $id)
    {
        try {
            $category = DB::select(
                'SELECT * FROM categories WHERE id = ? LIMIT 1',
                [$id]
            );

            if (empty($category)) {
                return $this->errorResponse('Categoría no encontrada.', 404);
            }

            // Verificar productos asociados con SQL puro
            $productCount = DB::select(
                'SELECT COUNT(*) AS total FROM products WHERE category_id = ?',
                [$id]
            );

            $total = $productCount[0]->total;

            if ($total > 0) {
                return $this->errorResponse(
                    'No se puede eliminar: esta categoría tiene ' . $total . ' productos asociados. Elimina o mueve los productos primero.',
                    400
                );
            }

            DB::delete(
                'DELETE FROM categories WHERE id = ?',
                [$id]
            );

            return $this->successResponse(
                null,
                'Categoría eliminada correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar la categoría.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }
}
