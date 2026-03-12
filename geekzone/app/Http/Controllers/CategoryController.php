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

            return response()->json([
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las categorías.',
                'error' => $e->getMessage(),
            ], 500);
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
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = Category::create($request->all());

        return response()->json([
            'message'   => 'Categoría creada correctamente.',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name'      => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'sometimes|nullable|string',
                'image_url'  => 'sometimes|nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $category->update($request->all());

            return response()->json([
                'message'   => 'Categoría actualizada correctamente.',
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la categoría.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'message' => 'Categoría no encontrada.',
                ], 404);
            }
        
            // Verificar si tiene productos asociados
            $quantityProducts = $category->products()->count();
            if ($quantityProducts > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar: esta categoría tiene ' . $quantityProducts . ' productos asociados. Elimina o mueve los productos primero.',
                ], 400);
            }
        
            $category->delete();
        
            return response()->json([
                'message' => 'Categoría eliminada correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la categoría.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
