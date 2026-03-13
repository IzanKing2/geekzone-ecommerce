<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function resumen()
    {
        return response()->json([
            'total_usuarios'  => User::count(),
            'total_productos' => Product::count(),
            'total_pedidos'   => Order::count(),
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

        return response()->json([
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

        return response()->json([
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

        return response()->json([
            'pedidos_por_cliente' => $pedidosPorCliente,
        ]);
    }
}

