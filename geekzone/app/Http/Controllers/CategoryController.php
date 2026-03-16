<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::withCount('products')
                ->orderBy('name')
                ->get();

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image_url'  => 'nullable|string|max:500',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $category = Category::create($request->all());

        return $this->successResponse(
            ['category' => $category],
            'Categoría creada correctamente.',
            201
        );
    }

    public function update(Request $request, int $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return $this->errorResponse('Categoría no encontrada.', 404);
            }

            $validator = Validator::make($request->all(), [
                'name'      => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'sometimes|nullable|string',
                'image_url'  => 'sometimes|nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $category->update($request->all());

            return $this->successResponse(
                ['category' => $category],
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

    public function destroy(int $id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return $this->errorResponse('Categoría no encontrada.', 404);
            }
        
            // Verificar si tiene productos asociados
            $quantityProducts = $category->products()->count();
            if ($quantityProducts > 0) {
                return $this->errorResponse(
                    'No se puede eliminar: esta categoría tiene ' . $quantityProducts . ' productos asociados. Elimina o mueve los productos primero.',
                    400
                );
            }
        
            $category->delete();
        
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
