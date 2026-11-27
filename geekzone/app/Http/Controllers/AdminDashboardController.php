<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class AdminDashboardController extends Controller
{
    /** Los pedidos cancelados no cuentan como ingresos. */
    private const ESTADO_CANCELADO = 'cancelado';

    /**
     * Cascarón del dashboard: no lleva datos. Se cargan por /api/admin/dashboard/*,
     * que exige JWT + rol admin (la vista en sí es pública, como el resto de páginas).
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    public function products(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('cat')) {
            $query->where('category_id', $request->cat);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%');
        }
        if ($request->boolean('sin_stock')) {
            $query->where('stock', 0);
        }
        if ($request->boolean('destacados')) {
            $query->where('featured', true);
        }

        $sort = match ($request->input('sort', 'recent')) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc' => ['name', 'asc'],
            default => ['created_at', 'desc'],
        };
        $query->orderBy($sort[0], $sort[1]);

        $products = $query->paginate(15)->appends($request->query());
        $categories = Category::orderBy('name')->get();
        $totalSinStock = Product::where('stock', 0)->count();

        return view('admin.products', compact('products', 'categories', 'totalSinStock'));
    }

    public function categories()
    {
        // Las ventas por categoría se cargan por API (dato sensible, requiere admin)
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        return view('admin.categories', compact('categories'));
    }

    // ── Helpers SQL ────────────────────────────────────────────────

    /** Expresión SQL "AAAA-MM" del mes de un pedido (MySQL en producción, SQLite en tests). */
    private function mesSql(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', orders.created_at)"
            : "DATE_FORMAT(orders.created_at, '%Y-%m')";
    }

    // ── API endpoints ──────────────────────────────────────────────

    #[OA\Get(
        path: '/api/admin/dashboard/resumen',
        summary: 'Resumen del dashboard (Administrador)',
        description: 'Devuelve estadísticas generales: total de usuarios, productos, pedidos e ingresos (sin contar pedidos cancelados). Requiere token JWT con rol de administrador.',
        tags: ['Dashboard (Administrador)'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Resumen obtenido correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'No tienes permisos de administrador')]
    public function resumen()
    {
        return $this->successResponse([
            'total_usuarios' => User::count(),
            'total_productos' => Product::count(),
            'total_pedidos' => Order::count(),
            'ingresos_totales' => round((float) Order::where('status', '!=', self::ESTADO_CANCELADO)->sum('total'), 2),
        ]);
    }

    #[OA\Get(
        path: '/api/admin/dashboard/ingresos',
        summary: 'Ingresos mensuales (Administrador)',
        description: 'Devuelve los ingresos agrupados por mes (sin contar pedidos cancelados). Por defecto los últimos 12 meses; se puede ajustar con `meses` (1-24). Requiere token JWT con rol de administrador.',
        tags: ['Dashboard (Administrador)'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'meses', in: 'query', required: false, description: 'Número de meses hacia atrás (1-24). Por defecto 12', schema: new OA\Schema(type: 'integer', default: 12, minimum: 1, maximum: 24))]
    #[OA\Response(response: 200, description: 'Ingresos mensuales obtenidos correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'No tienes permisos de administrador')]
    public function ingresos()
    {
        $meses = min(max((int) request()->query('meses', 12), 1), 24);

        $ingresos = Order::select(
            DB::raw($this->mesSql().' as mes'),
            DB::raw('SUM(total) as ingresos')
        )
            ->where('status', '!=', self::ESTADO_CANCELADO)
            ->where('created_at', '>=', now()->subMonths($meses))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(fn ($fila) => [
                'mes' => $fila->getAttribute('mes'),
                'ingresos' => (float) $fila->getAttribute('ingresos'),
            ]);

        return $this->successResponse([
            'ingresos_mensuales' => $ingresos,
        ]);
    }

    #[OA\Get(
        path: '/api/admin/dashboard/top-productos',
        summary: 'Top 5 productos más vendidos (Administrador)',
        description: 'Devuelve los 5 productos más vendidos con sus ingresos generados (sin contar pedidos cancelados; incluye productos ya eliminados). Requiere token JWT con rol de administrador.',
        tags: ['Dashboard (Administrador)'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Top productos obtenidos correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'No tienes permisos de administrador')]
    public function topProductos()
    {
        $topProductos = OrderDetail::select(
            'order_details.product_id',
            DB::raw('SUM(order_details.quantity) as total_vendido'),
            DB::raw('SUM(order_details.price * order_details.quantity) as ingresos_generados')
        )
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.status', '!=', self::ESTADO_CANCELADO)
            ->groupBy('order_details.product_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->with(['product' => fn ($q) => $q->withTrashed()->select('id', 'name', 'image_url')])
            ->get();

        return $this->successResponse([
            'top_productos' => $topProductos,
        ]);
    }

    #[OA\Get(
        path: '/api/admin/dashboard/ventas-por-categoria',
        summary: 'Ventas por categoría (Administrador)',
        description: 'Devuelve el total vendido por categoría, de mayor a menor (sin contar pedidos cancelados). Requiere token JWT con rol de administrador.',
        tags: ['Dashboard (Administrador)'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Ventas por categoría obtenidas correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'No tienes permisos de administrador')]
    public function ventasPorCategoria()
    {
        $ventas = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', '!=', self::ESTADO_CANCELADO)
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_details.quantity * order_details.price) as total_ventas')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_ventas')
            ->get()
            ->map(fn ($fila) => [
                'id' => $fila->id,
                'name' => $fila->name,
                'total_ventas' => (float) $fila->total_ventas,
            ]);

        return $this->successResponse([
            'ventas_por_categoria' => $ventas,
        ]);
    }

    #[OA\Get(
        path: '/api/admin/dashboard/pedidos-recientes',
        summary: 'Últimos pedidos (Administrador)',
        description: 'Devuelve los 8 pedidos más recientes con el cliente. Requiere token JWT con rol de administrador.',
        tags: ['Dashboard (Administrador)'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Pedidos recientes obtenidos correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'No tienes permisos de administrador')]
    public function pedidosRecientes()
    {
        $pedidos = Order::with('user:id,name,email')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get(['id', 'user_id', 'status', 'total', 'created_at']);

        return $this->successResponse([
            'pedidos_recientes' => $pedidos,
        ]);
    }

    #[OA\Get(
        path: '/api/admin/dashboard/pedidos-por-cliente',
        summary: 'Pedidos por cliente (Administrador)',
        description: 'Devuelve un ranking de clientes ordenado por gasto total. Requiere token JWT con rol de administrador.',
        tags: ['Dashboard (Administrador)'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Pedidos por cliente obtenidos correctamente')]
    #[OA\Response(response: 401, description: 'No autorizado. Token inválido o no existe')]
    #[OA\Response(response: 403, description: 'No tienes permisos de administrador')]
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
