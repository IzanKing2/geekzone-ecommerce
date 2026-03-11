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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'])
                  ->default('pendiente');
            $table->decimal('total', 10, 2);
            $table->timestamps();
            $table->index('user_id');
        });

        // ——————————————————————————————————————————————————————————————————————
        // TABLA DETALLE_PEDIDOS (líneas del pedido)
        // ——————————————————————————————————————————————————————————————————————
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')
                  ->constrained('pedidos')
                  ->onDelete('cascade');
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('restrict');
            $table->integer('cantidad')->unsigned();
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
        Schema::dropIfExists('pedidos');
    }
};
