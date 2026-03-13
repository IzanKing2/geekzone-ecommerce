<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase, CreateBaseData;

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE AÑADIR PRODUCTO
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Test para obtener el carrito ——————————————————————————————————————————————————————————————————
    public function test_get_cart(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsUser();

        $response = $this->getJson('/api/carrito', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cart',
                'total',
                'items_count'
            ]);
    }

    // ———— Test para añadir un producto al carrito ——————————————————————————————————————————————————————————————————
    public function test_add_product_to_cart(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/carrito', [
            'product_id' => $product1->id,
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'cart_item' => [
                    'user_id',
                    'product_id',
                    'quantity',
                ],
            ]);
    }

    // ———— Test para actualizar la cantidad de un producto en el carrito ——————————————————————————————————————————————————————————————————
    public function test_update_product_quantity_in_cart(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/carrito', [
            'product_id' => $product1->id,
        ], $headers);

        $response = $this->putJson('/api/carrito/' . $product1->id, [
            'quantity' => 2,
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'cart_item' => [
                    'quantity',
                ],
            ]);

        // Verificar que la cantidad se actualizó correctamente
        $this->assertDatabaseHas('carts', [
            'id' => $product1->id,
            'quantity' => 2,
        ]);
    }

    // ———— Test para eliminar un producto del carrito ——————————————————————————————————————————————————————————————————
    public function test_delete_product_from_cart(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/carrito', [
            'product_id' => $product1->id,
        ], $headers);

        $response = $this->deleteJson('/api/carrito/' . $product1->id, [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Item eliminado del carrito correctamente.']);

        // Verificar que el producto fue eliminado del carrito
        $this->assertDatabaseMissing('carts', [
            'id' => $product1->id,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE CARRITO (USUARIO)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Test para obtener el carrito ——————————————————————————————————————————————————————————————————
    public function test_cart_without_authentication(): void
    {
        $response = $this->getJson('/api/carrito');

        $response->assertStatus(401);
    }
}
