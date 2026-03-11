<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'categories' => $categories,
        ], 200);
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS DE ADMINISTRACIÓN
    // ——————————————————————————————————————————————————————————————————————————

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string',
            'imagen_url'  => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error de validación.',
                'errores' => $validator->errors(),
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
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre'      => 'sometimes|string|max:255|unique:categorias,nombre,' . $category->id,
            'descripcion' => 'sometimes|nullable|string',
            'imagen_url'  => 'sometimes|nullable|string|max:500',
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
    }

    public function destroy(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        // Verificar si tiene productos asociados
        $cantidadProductos = $category->products()->count();
        if ($cantidadProductos > 0) {
            return response()->json([
                'message' => 'No se puede eliminar: esta categoría tiene ' . $cantidadProductos . ' productos asociados. Elimina o mueve los productos primero.',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ], 200);
    }
}
