<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, CreateBaseData;

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE LISTADO
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Listado de productos exitoso ——————————————————————————————————————————————————————————————————
    public function test_list_products_successfully(): void
    {
        [$marvel] = 
        $this->createCategoriesForTest();
        $this->createProductsForTest($marvel->id);

        $response = $this->getJson('/api/productos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                            'image_url',
                            'category_id',
                        ],
                    ],
                    'total',
                ],
            ]);
    }

    // ———— Test para filtrar por categoría ——————————————————————————————————————————————————————————————————
    public function test_filter_products_by_category(): void
    {
        [$marvel, $futbol] =
        $this->createCategoriesForTest();
        $this->createProductsForTest($marvel->id);
        $this->createProductsForTest($futbol->id);

        $response = $this->getJson('/api/productos?category_id=' . $marvel->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                            'image_url',
                            'category_id',
                        ],
                    ],
                    'total',
                ],
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE DETALLE
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Detalle de producto exitoso ——————————————————————————————————————————————————————————————————
    public function test_show_product_successfully(): void
    {
        [$marvel] = 
        $this->createCategoriesForTest();
        $this->createProductsForTest($marvel->id);

        $response = $this->getJson('/api/productos/' . $marvel->products->first()->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'product' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock',
                        'image_url',
                        'category_id',
                    ],
                ],
            ]);
    }

    // ———— Test para obtener un producto inexistente ——————————————————————————————————————————————————————————————————
    public function test_show_product_not_found(): void
    {
        $response = $this->getJson('/api/productos/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Producto no encontrado.',
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEST SOFT DELETES
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Test para soft delete de producto exitoso ——————————————————————————————————————————————————————————————————
    public function test_deleted_products_do_not_appear(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);

        $product1->delete();

        // Listar productos → debería mostrar solo 2
        $response = $this->getJson('/api/productos');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.total'));


        // Verificar que el producto eliminado sigue en la BD
        // pero con deleted_at marcado
        $this->assertSoftDeleted('products', ['id' => $product1->id]);
    }
}
