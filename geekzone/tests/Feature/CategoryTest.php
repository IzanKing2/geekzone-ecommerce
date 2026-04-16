<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase, CreateBaseData;

    // ══════════════════════════════════════════════════════════════════════════
    // GET /api/categorias  —  índice público
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Listado con categorías existentes ——————————————————————————————————
    public function test_index_returns_all_categories(): void
    {
        $this->createCategoriesForTest(); // crea Marvel y Fútbol

        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'categories' => [
                        '*' => ['id', 'name', 'description', 'image_url', 'products_count'],
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data.categories'));
    }

    // ———— Listado vacío cuando no hay categorías —————————————————————————————
    public function test_index_returns_empty_when_no_categories(): void
    {
        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data.categories'));
    }

    // ———— El conteo de productos es correcto (JOIN) ———————————————————————————
    public function test_index_includes_correct_products_count(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $this->createProductsForTest($marvel->id); // 3 productos en Marvel

        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');

        $marvelData = collect($categories)->firstWhere('name', 'Marvel');
        $futbolData = collect($categories)->firstWhere('name', 'Fútbol');

        $this->assertEquals(3, $marvelData['products_count']);
        $this->assertEquals(0, $futbolData['products_count']);
    }

    // ———— El listado está ordenado por nombre (ASC) ———————————————————————————
    public function test_index_is_ordered_by_name(): void
    {
        $this->createCategoriesForTest(); // Marvel, Fútbol

        $response = $this->getJson('/api/categorias');
        $names = array_column($response->json('data.categories'), 'name');

        $sorted = $names;
        sort($sorted);
        $this->assertEquals($sorted, $names);
    }

    // ———— El endpoint es público (sin token) —————————————————————————————————
    public function test_index_is_publicly_accessible(): void
    {
        $response = $this->getJson('/api/categorias');
        $response->assertStatus(200);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // POST /api/categorias  —  crear (admin)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Creación exitosa —————————————————————————————————————————————————————
    public function test_store_creates_category_successfully(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'name'        => 'Anime',
            'description' => 'Productos de anime y manga',
            'image_url'   => 'img/test/anime.jpg',
        ], $headers);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Categoría creada correctamente.'])
            ->assertJsonStructure([
                'data' => [
                    'category' => ['id', 'name', 'description', 'image_url'],
                ],
            ]);
    }

    // ———— La categoría se persiste en la base de datos ————————————————————————
    public function test_store_persists_in_database(): void
    {
        $headers = $this->loginAsAdmin();

        $this->postJson('/api/categorias', [
            'name'        => 'Anime',
            'description' => 'Productos de anime y manga',
            'image_url'   => 'img/test/anime.jpg',
        ], $headers);

        $this->assertDatabaseHas('categories', [
            'name'        => 'Anime',
            'description' => 'Productos de anime y manga',
        ]);
    }

    // ———— La respuesta incluye los datos de la categoría creada ——————————————
    public function test_store_returns_created_category_data(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'name' => 'Anime',
        ], $headers);

        $category = $response->json('data.category');
        $this->assertEquals('Anime', $category['name']);
        $this->assertNotNull($category['id']);
    }

    // ———— Creación solo con el nombre (campos opcionales nulos) ———————————————
    public function test_store_with_only_required_fields(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'name' => 'Solo Nombre',
        ], $headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', ['name' => 'Solo Nombre']);
    }

    // ———— No se puede crear sin nombre —————————————————————————————————————————
    public function test_store_fails_without_name(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'description' => 'Sin nombre',
        ], $headers);

        $response->assertStatus(422);
    }

    // ———— No se puede crear con nombre duplicado ——————————————————————————————
    public function test_store_fails_with_duplicate_name(): void
    {
        $this->createCategoriesForTest(); // crea Marvel
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'name' => 'Marvel',
        ], $headers);

        $response->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'Ya existe una categoría con ese nombre.');
    }

    // ———— No se puede crear con nombre demasiado largo ————————————————————————
    public function test_store_fails_with_name_too_long(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->postJson('/api/categorias', [
            'name' => str_repeat('a', 256),
        ], $headers);

        $response->assertStatus(422);
    }

    // ———— Un usuario normal no puede crear categorías ————————————————————————
    public function test_store_forbidden_for_regular_user(): void
    {
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/categorias', [
            'name' => 'Intento No Autorizado',
        ], $headers);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('categories', ['name' => 'Intento No Autorizado']);
    }

    // ———— Sin autenticación no se puede crear ————————————————————————————————
    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/categorias', [
            'name' => 'Sin Token',
        ]);

        $response->assertStatus(401);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PUT /api/categorias/{id}  —  actualizar (admin)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Actualización completa exitosa ——————————————————————————————————————
    public function test_update_modifies_category_successfully(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $response = $this->putJson('/api/categorias/' . $marvel->id, [
            'name'        => 'Marvel Actualizado',
            'description' => 'Nueva descripción',
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Categoría actualizada correctamente.'])
            ->assertJsonStructure([
                'data' => [
                    'category' => ['id', 'name', 'description', 'image_url'],
                ],
            ]);
    }

    // ———— Los cambios se persisten en la base de datos ————————————————————————
    public function test_update_persists_changes_in_database(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $this->putJson('/api/categorias/' . $marvel->id, [
            'name' => 'Marvel Editado',
        ], $headers);

        $this->assertDatabaseHas('categories', [
            'id'   => $marvel->id,
            'name' => 'Marvel Editado',
        ]);
    }

    // ———— Actualización parcial: solo description —————————————————————————————
    public function test_update_partial_fields_only_description(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $this->putJson('/api/categorias/' . $marvel->id, [
            'description' => 'Solo actualizando descripción',
        ], $headers);

        // El nombre original debe mantenerse
        $this->assertDatabaseHas('categories', [
            'id'          => $marvel->id,
            'name'        => 'Marvel',
            'description' => 'Solo actualizando descripción',
        ]);
    }

    // ———— Actualización parcial: solo image_url ——————————————————————————————
    public function test_update_partial_fields_only_image_url(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $this->putJson('/api/categorias/' . $marvel->id, [
            'image_url' => 'img/new/marvel.jpg',
        ], $headers);

        $this->assertDatabaseHas('categories', [
            'id'        => $marvel->id,
            'image_url' => 'img/new/marvel.jpg',
        ]);
    }

    // ———— La respuesta contiene los datos actualizados ————————————————————————
    public function test_update_returns_updated_data(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $response = $this->putJson('/api/categorias/' . $marvel->id, [
            'name' => 'Marvel Nuevo',
        ], $headers);

        $this->assertEquals('Marvel Nuevo', $response->json('data.category.name'));
    }

    // ———— No se puede actualizar con nombre duplicado de otra categoría ———————
    public function test_update_fails_with_duplicate_name(): void
    {
        [$marvel, $futbol] = $this->createCategoriesForTest();
        $headers           = $this->loginAsAdmin();

        $response = $this->putJson('/api/categorias/' . $futbol->id, [
            'name' => 'Marvel', // nombre ya usado por otra categoría
        ], $headers);

        $response->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'Ya existe una categoría con ese nombre.');
    }

    // ———— Sí se puede actualizar con el mismo nombre de la propia categoría ——
    public function test_update_allows_same_name_for_same_category(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $response = $this->putJson('/api/categorias/' . $marvel->id, [
            'name' => 'Marvel', // mismo nombre, mismo id
        ], $headers);

        $response->assertStatus(200);
    }

    // ———— Devuelve 404 si la categoría no existe —————————————————————————————
    public function test_update_returns_404_for_nonexistent_category(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->putJson('/api/categorias/9999', [
            'name' => 'Inexistente',
        ], $headers);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Categoría no encontrada.']);
    }

    // ———— Un usuario normal no puede actualizar categorías ———————————————————
    public function test_update_forbidden_for_regular_user(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsUser();

        $response = $this->putJson('/api/categorias/' . $marvel->id, [
            'name' => 'Intento Ilegal',
        ], $headers);

        $response->assertStatus(403);
    }

    // ———— Sin autenticación no se puede actualizar ———————————————————————————
    public function test_update_requires_authentication(): void
    {
        [$marvel] = $this->createCategoriesForTest();

        $response = $this->putJson('/api/categorias/' . $marvel->id, [
            'name' => 'Sin Token',
        ]);

        $response->assertStatus(401);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DELETE /api/categorias/{id}  —  eliminar (admin)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Eliminación exitosa de categoría sin productos —————————————————————
    public function test_destroy_deletes_empty_category(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $response = $this->deleteJson('/api/categorias/' . $marvel->id, [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Categoría eliminada correctamente.']);
    }

    // ———— La categoría desaparece de la base de datos —————————————————————————
    public function test_destroy_removes_from_database(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $this->deleteJson('/api/categorias/' . $marvel->id, [], $headers);

        $this->assertDatabaseMissing('categories', ['id' => $marvel->id]);
    }

    // ———— No se puede eliminar si tiene productos asociados ——————————————————
    public function test_destroy_fails_when_category_has_products(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $this->createProductsForTest($marvel->id); // 3 productos asociados
        $headers = $this->loginAsAdmin();

        $response = $this->deleteJson('/api/categorias/' . $marvel->id, [], $headers);

        $response->assertStatus(400);
        // El mensaje menciona la cantidad de productos bloqueantes
        $this->assertStringContainsString('3', $response->json('message'));
    }

    // ———— La categoría no se elimina si tiene productos ——————————————————————
    public function test_destroy_does_not_delete_when_has_products(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $this->createProductsForTest($marvel->id);
        $headers = $this->loginAsAdmin();

        $this->deleteJson('/api/categorias/' . $marvel->id, [], $headers);

        $this->assertDatabaseHas('categories', ['id' => $marvel->id]);
    }

    // ———— Devuelve 404 si la categoría no existe —————————————————————————————
    public function test_destroy_returns_404_for_nonexistent_category(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->deleteJson('/api/categorias/9999', [], $headers);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Categoría no encontrada.']);
    }

    // ———— Un usuario normal no puede eliminar categorías —————————————————————
    public function test_destroy_forbidden_for_regular_user(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsUser();

        $response = $this->deleteJson('/api/categorias/' . $marvel->id, [], $headers);

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $marvel->id]);
    }

    // ———— Sin autenticación no se puede eliminar ——————————————————————————————
    public function test_destroy_requires_authentication(): void
    {
        [$marvel] = $this->createCategoriesForTest();

        $response = $this->deleteJson('/api/categorias/' . $marvel->id);

        $response->assertStatus(401);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INTEGRIDAD — flujo completo
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Crear → leer → actualizar → eliminar ————————————————————————————————
    public function test_full_crud_lifecycle(): void
    {
        $headers = $this->loginAsAdmin();

        // Crear
        $createResponse = $this->postJson('/api/categorias', [
            'name'        => 'Ciclo Completo',
            'description' => 'Categoría para test de ciclo',
        ], $headers);
        $createResponse->assertStatus(201);
        $id = $createResponse->json('data.category.id');
        $this->assertNotNull($id);

        // Aparece en el listado
        $indexResponse = $this->getJson('/api/categorias');
        $names = array_column($indexResponse->json('data.categories'), 'name');
        $this->assertContains('Ciclo Completo', $names);

        // Actualizar
        $updateResponse = $this->putJson('/api/categorias/' . $id, [
            'name' => 'Ciclo Completo Actualizado',
        ], $headers);
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'id'   => $id,
            'name' => 'Ciclo Completo Actualizado',
        ]);

        // Eliminar
        $deleteResponse = $this->deleteJson('/api/categorias/' . $id, [], $headers);
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $id]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INTEGRIDAD — SQL puro (verificación directa en BD)
    // ══════════════════════════════════════════════════════════════════════════

    // ———— El INSERT vía SQL genera los campos created_at y updated_at —————————
    public function test_store_sets_timestamps_via_raw_sql(): void
    {
        $headers = $this->loginAsAdmin();
        $this->postJson('/api/categorias', ['name' => 'Con Timestamps'], $headers);

        $row = DB::select('SELECT created_at, updated_at FROM categories WHERE name = ?', ['Con Timestamps']);

        $this->assertNotEmpty($row);
        $this->assertNotNull($row[0]->created_at);
        $this->assertNotNull($row[0]->updated_at);
    }

    // ———— El UPDATE modifica updated_at ———————————————————————————————————————
    public function test_update_refreshes_updated_at_via_raw_sql(): void
    {
        [$marvel] = $this->createCategoriesForTest();
        $headers  = $this->loginAsAdmin();

        $before = DB::select('SELECT updated_at FROM categories WHERE id = ?', [$marvel->id]);

        // Esperar 1 segundo para que el timestamp sea diferente
        sleep(1);

        $this->putJson('/api/categorias/' . $marvel->id, ['name' => 'Marvel v2'], $headers);

        $after = DB::select('SELECT updated_at FROM categories WHERE id = ?', [$marvel->id]);

        $this->assertNotEquals($before[0]->updated_at, $after[0]->updated_at);
    }

    // ———— El DELETE borra exactamente 1 fila ——————————————————————————————————
    public function test_destroy_deletes_exactly_one_row(): void
    {
        [$marvel, $futbol] = $this->createCategoriesForTest();
        $headers           = $this->loginAsAdmin();

        $this->deleteJson('/api/categorias/' . $marvel->id, [], $headers);

        $count = DB::select('SELECT COUNT(*) AS total FROM categories');
        $this->assertEquals(1, $count[0]->total); // solo queda Fútbol
    }
}
