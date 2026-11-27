<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use CreateBaseData, RefreshDatabase;

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
            ->assertJson(['success' => true, 'errors' => null])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['cart', 'total', 'items_count'],
                'errors',
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
            ->assertJson(['success' => true, 'errors' => null])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart_item' => ['user_id', 'product_id', 'quantity'],
                ],
                'errors',
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

        $response = $this->putJson('/api/carrito/'.$product1->id, [
            'quantity' => 2,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['cart_item' => ['quantity']],
                'errors',
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

        $response = $this->deleteJson('/api/carrito/'.$product1->id, [], $headers);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Item eliminado del carrito correctamente.', 'data' => null]);

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

    // ———— Errores con el formato estándar ——————————————————————————————————————————————————————————————————
    public function test_not_enough_stock_returns_standard_error(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id); // stock 10
        $headers = $this->loginAsUser();

        $this->postJson('/api/carrito', ['product_id' => $product1->id, 'quantity' => 11], $headers)
            ->assertStatus(400)
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors'])
            ->assertJsonPath('message', 'No hay suficiente stock. Disponible: 10');
    }

    public function test_add_validation_error_returns_standard_error(): void
    {
        $headers = $this->loginAsUser();

        $this->postJson('/api/carrito', [], $headers)
            ->assertStatus(422)
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors' => ['product_id']]);

        $this->postJson('/api/carrito', ['product_id' => 999999], $headers)
            ->assertStatus(422)
            ->assertJsonValidationErrors('product_id');
    }

    public function test_update_validation_error_returns_standard_error(): void
    {
        $headers = $this->loginAsUser();

        $this->putJson('/api/carrito/1', ['quantity' => 0], $headers)
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message', 'data', 'errors' => ['quantity']]);
    }

    public function test_missing_cart_item_returns_standard_404(): void
    {
        $headers = $this->loginAsUser();

        foreach (['putJson' => ['quantity' => 1], 'deleteJson' => []] as $metodo => $datos) {
            $this->{$metodo}('/api/carrito/999999', $datos, $headers)
                ->assertStatus(404)
                ->assertJson(['success' => false, 'message' => 'Item no encontrado en el carrito.', 'data' => null])
                ->assertJsonStructure(['success', 'message', 'data', 'errors']);
        }
    }
}
