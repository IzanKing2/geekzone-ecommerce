<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'products' => $products,
            'total' => $products->count(),
        ]);
    }

    public function show(int $id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        return response()->json([
            'product' => $product,
        ]);
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS DE ADMINISTRACIÓN (protegidos con middleware admin)
    // ——————————————————————————————————————————————————————————————————————————

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'       => 'required|numeric|min:0.01',
            'stock'        => 'required|integer|min:0',
            'image_url'   => 'nullable|string|max:500',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::create($request->all());
        $product->load('category');

        return response()->json([
            'message'  => 'Producto creado correctamente.',
            'product' => $product,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|string|max:255',
            'description'  => 'sometimes|nullable|string',
            'price'       => 'sometimes|numeric|min:0.01',
            'stock'        => 'sometimes|integer|min:0',
            'image_url'   => 'sometimes|nullable|string|max:500',
            'category_id' => 'sometimes|integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product->update($request->all());
        $product->load('category');

        return response()->json([
            'message'  => 'Producto actualizado correctamente.',
            'product' => $product,
        ]);
    }

    public function destroy(int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.',
        ]);
    }
}
