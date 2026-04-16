<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase, CreateBaseData;


    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE PRODUCTOS (ADMIN)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Test para crear un producto ——————————————————————————————————————————————————————————————————
    public function test_create_product(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/productos', [
            'name' => 'Producto Test',
            'description' => 'Descripción del producto test',
            'price' => 10.00,
            'stock' => 10,
            'image_url' => 'img/test/producto.jpg',
            'category_id' => $marvel->id,
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
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

    // ———— Usuario no puede crear producto ——————————————————————————————————————————————————————————————————
    public function test_user_cannot_create_product(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/productos', [
            'name'       => 'Intento Ilegal',
            'description'  => 'Un usuario normal no debería poder crear',
            'price'       => 10.00,
            'stock'        => 5,
            'category_id' => $marvel->id,
        ], $headers);

        $response->assertStatus(403);
    }

    // ———— Test para actualizar un producto ——————————————————————————————————————————————————————————————————
    public function test_admin_puede_editar_producto(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsAdmin();

        // Editar el nombre del producto
        $response = $this->putJson('/api/productos/' . $product1->id, [
            'name' => 'Figura Spider-Man Edición Especial',
            'price' => 39.99,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Producto actualizado correctamente.']);

        // Verificar que los cambios se guardaron
        $this->assertDatabaseHas('products', [
            'id'     => $product1->id,
            'name' => 'Figura Spider-Man Edición Especial',
            'price' => 39.99,
        ]);
    }

    // ———— Admin puede eliminar producto ——————————————————————————————————————————————————————————————————
    public function test_admin_can_delete_product(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        [$product1] = $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsAdmin();

        $response = $this->deleteJson('/api/productos/' . $product1->id, [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Producto eliminado correctamente.']);

        // Verificar que el producto fue soft-deleted
        // (sigue en la BD pero con deleted_at rellenado)
        $this->assertSoftDeleted('products', ['id' => $product1->id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE CATEGORIAS (ADMIN)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Test para crear una categoría ——————————————————————————————————————————————————————————————————
    public function test_admin_can_create_category(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'name'      => 'Anime',
            'description' => 'Productos de anime y manga',
            'image_url'  => 'img/test/anime.jpg',
        ], $headers);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Categoría creada correctamente.']);

        $this->assertDatabaseHas('categories', [
            'name' => 'Anime',
        ]);
    }
    
    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE DASHBOARD
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Test para obtener el resumen de la dashboard ——————————————————————————————————————————————————————————————————
    public function test_get_dashboard_summary(): void
    {
        
        $headers = $this->loginAsAdmin();

        $response = $this->getJson('/api/admin/dashboard/resumen', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_usuarios',
                    'total_productos',
                    'total_pedidos',
                    'ingresos_totales',
                ],
            ]);
    }

    // ———— Usuario no puede acceder al dashboard ——————————————————————————————————————————————————————————————————
    public function test_user_cannot_access_dashboard(): void
    {
        $headers = $this->loginAsUser();

        $response = $this->getJson('/api/admin/dashboard/resumen', $headers);

        $response->assertStatus(403);
    }
    }   