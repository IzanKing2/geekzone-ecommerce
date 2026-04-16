<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── Stats ──────────────────────────────────────────────────
        $totalUsuarios  = User::count();
        $totalProductos = Product::count();
        $totalPedidos   = Order::count();
        $ingresosTotales = round(Order::sum('total'), 2);

        // ── Ventas por categoría ───────────────────────────────────
        $ventasPorCategoria = OrderDetail::select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_details.quantity * order_details.price) as total_ventas')
            )
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_ventas')
            ->get();

        $maxVentas = $ventasPorCategoria->max('total_ventas') ?: 1;

        // ── Ingresos últimos 6 meses ───────────────────────────────
        $ingresosMensuales = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"),
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as mes_label"),
                DB::raw('SUM(total) as ingresos')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mes', 'mes_label')
            ->orderBy('mes')
            ->get();

        $maxIngresos = $ingresosMensuales->max('ingresos') ?: 1;

        // ── Top 5 productos más vendidos ───────────────────────────
        $topProductos = OrderDetail::select(
                'product_id',
                DB::raw('SUM(quantity) as total_vendido'),
                DB::raw('SUM(price * quantity) as ingresos_generados')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->with('product:id,name,image_url')
            ->get();

        // ── Pedidos recientes ──────────────────────────────────────
        $pedidosRecientes = Order::with('user:id,name,email')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalProductos',
            'totalPedidos',
            'ingresosTotales',
            'ventasPorCategoria',
            'maxVentas',
            'ingresosMensuales',
            'maxIngresos',
            'topProductos',
            'pedidosRecientes'
        ));
    }

    public function products(\Illuminate\Http\Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('cat')) {
            $query->where('category_id', $request->cat);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->boolean('sin_stock')) {
            $query->where('stock', 0);
        }
        if ($request->boolean('destacados')) {
            $query->where('featured', true);
        }

        $sort = match($request->input('sort', 'recent')) {
            'price_asc'  => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc'   => ['name', 'asc'],
            default      => ['created_at', 'desc'],
        };
        $query->orderBy($sort[0], $sort[1]);

        $products   = $query->paginate(15)->appends($request->query());
        $categories = Category::orderBy('name')->get();
        $totalSinStock = Product::where('stock', 0)->count();

        return view('admin.products', compact('products', 'categories', 'totalSinStock'));
    }

    public function categories()
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        // Añadir ventas totales por categoría
        $ventasPorCategoria = OrderDetail::select(
                'products.category_id',
                DB::raw('SUM(order_details.quantity * order_details.price) as total_ventas')
            )
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->groupBy('products.category_id')
            ->pluck('total_ventas', 'category_id');

        $categories->each(function ($cat) use ($ventasPorCategoria) {
            $cat->total_ventas = $ventasPorCategoria[$cat->id] ?? 0;
        });

        return view('admin.categories', compact('categories'));
    }

    // ── API endpoints ──────────────────────────────────────────────

    public function resumen()
    {
        return $this->successResponse([
            'total_usuarios'   => User::count(),
            'total_productos'  => Product::count(),
            'total_pedidos'    => Order::count(),
            'ingresos_totales' => round(Order::sum('total'), 2),
        ]);
    }

    public function ingresos()
    {
        $ingresos = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"),
                DB::raw('SUM(total) as ingresos')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return $this->successResponse([
            'ingresos_mensuales' => $ingresos,
        ]);
    }

    public function topProductos()
    {
        $topProductos = OrderDetail::select(
                'product_id',
                DB::raw('SUM(quantity) as total_vendido'),
                DB::raw('SUM(price * quantity) as ingresos_generados')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->with('product:id,name,image_url')
            ->get();

        return $this->successResponse([
            'top_productos' => $topProductos,
        ]);
    }

    public function pedidosPorCliente()
    {
        $pedidosPorCliente = Order::select(
                'user_id',
                DB::raw('COUNT(*) as numero_pedidos'),
                DB::raw('SUM(total) as total_gastado')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_gastado')
            ->with('user:id,name,email')
            ->get();

        return $this->successResponse([
            'pedidos_por_cliente' => $pedidosPorCliente,
        ]);
    }
}
