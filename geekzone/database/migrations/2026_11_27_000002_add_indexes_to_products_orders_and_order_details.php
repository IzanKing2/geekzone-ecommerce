<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices para los filtros y ordenaciones reales de la aplicación:
     * - products: destacados, precio y novedades (catálogo y panel admin).
     * - orders: dashboard (ingresos por mes, filtro por estado).
     * - order_details: un producto aparece una sola vez por pedido.
     */
    public function up(): void
    {
        $duplicados = DB::table('order_details')
            ->select('order_id', 'product_id')
            ->groupBy('order_id', 'product_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicados > 0) {
            throw new RuntimeException(
                "No se puede añadir order_details(order_id, product_id) único: hay {$duplicados} pedidos con el mismo producto repetido. Revísalos y vuelve a migrar."
            );
        }

        Schema::table('products', function (Blueprint $table) {
            $table->index('featured');
            $table->index('price');
            $table->index('created_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('status');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        // MySQL usa el índice único para la clave foránea de order_id (borró el índice implícito
        // al crearlo): hay que recuperar un índice simple antes de quitar el único.
        Schema::table('order_details', function (Blueprint $table) {
            $table->index('order_id');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['featured']);
            $table->dropIndex(['price']);
            $table->dropIndex(['created_at']);
        });
    }
};
