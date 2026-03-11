<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ——————————————————————————————————————————————————————————————————————
        // TABLA PEDIDOS (cabecera del pedido)
        // ——————————————————————————————————————————————————————————————————————
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('status', ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'])
                  ->default('pendiente');
            $table->decimal('total', 10, 2);
            $table->timestamps();
            $table->index('user_id');
        });

        // ——————————————————————————————————————————————————————————————————————
        // TABLA DETALLE_PEDIDOS (líneas del pedido)
        // ——————————————————————————————————————————————————————————————————————
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('restrict');
            $table->integer('quantity')->unsigned();
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
    }
};
