<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, CreateBaseData;

    // ══════════════════════════════════════════════════════════════════════════════════════════════════
    // TEST DE PEDIDOS
    // ══════════════════════════════════════════════════════════════════════════════════════════════════

    // ———— Test para crear un pedido —————————————————————————————————————————
    public function test_create_order_from_cart(): void
    {
        // Preparar datos
        [$marvel] = $this->createCategoriesForTest();
        [$product1, $product2] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsUser();

        // Añadir 2 unidades del primer producto y 1 del segundo al carrito
        $this->postJson('/api/carrito', [
            'product_id' => $product1->id,
            'quantity' => 2,
        ], $headers);

        $this->postJson('/api/carrito', [
            'product_id' => $product2->id,
        ], $headers);

        // Crear el pedido
        $response = $this->postJson('/api/pedidos', [], $headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => ['id', 'user_id', 'status', 'total'],
            ]);

        // Verificar que el pedido está en la BD
        $this->assertDatabaseHas('orders', [
            'status' => 'pendiente',
        ]);

        // Verificar que el carrito se vació
        $this->assertEquals(0, Cart::count());

        // Verificar que el stock se descontó:
        // producto1 tenía 10, se pidieron 2 → debe quedar 8
        $product1->refresh();
        $this->assertEquals(8, $product1->stock);

        // producto2 tenía 5, se pidió 1 → debe quedar 4
        $product2->refresh();
        $this->assertEquals(4, $product2->stock);
    }

    // ———— Test para crear un pedido con carrito vacío —————————————————————————————————————————
    public function test_create_order_with_empty_cart(): void
    {
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/pedidos', [], $headers);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No hay items en el carrito. Añade productos para crear un pedido.',
            ]);
    }

    // ———— Test para listar los pedidos del usuario —————————————————————————————————————————
    public function test_list_orders_for_user(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsUser();

        // Crear un pedido (añadir al carrito y confirmar)
        $this->postJson('/api/carrito', [
            'product_id' => $product1->id,
            'quantity' => 1,
        ], $headers);

        $this->postJson('/api/pedidos', [], $headers);

        // Listar pedidos
        $response = $this->getJson('/api/pedidos', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'orders' => [
                    '*' => ['id', 'user_id', 'status', 'total'],
                ],
                'total',
            ]);

        $this->assertEquals(1, $response->json('total'));
    }

    // ══════════════════════════════════════════════════════════════════════════════════════════════════
    // TEST SIN AUTENTICACIÓN
    // ══════════════════════════════════════════════════════════════════════════════════════════════════

    // ———— Test para listar los pedidos sin autenticación —————————————————————————————————————————
    public function test_list_orders_without_authentication(): void
    {
        $response = $this->getJson('/api/pedidos');

        $response->assertStatus(401);
    }
}
