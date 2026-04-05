<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
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

            $paginacion = $this->GetPaginacion($request);

            $products = $query
                ->orderBy('created_at', 'desc')
                ->paginate($paginacion);

            return $this->successResponse([
                'items'      => $products->items(),
                'total'      => $products->total(),
                'per_page'   => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'  => $products->lastPage(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener los productos.',
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }

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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'       => 'required|string|max:255',
                'description'  => 'nullable|string',
                'price'       => 'required|numeric|min:0.01',
                'stock'        => 'required|integer|min:0',
                'image_url'   => 'nullable|string|max:500',
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

    public function update(Request $request, int $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return $this->errorResponse('Producto no encontrado.', 404);
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

    //PAGINACION PAGINA
    public function GetPaginacion(Request $request): int
    {
        $arraypaginaciones = [6,12,24];
        $paginacion = (int) $request -> input('pagn', 12);
        return in_array($paginacion,$arraypaginaciones) ? $paginacion : 12;
    }
}
