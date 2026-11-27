<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    private array $headers;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headers = $this->loginAsUser();
        $this->user = User::where('email', 'test@geekzone.com')->first();
    }

    private function envelope(): array
    {
        return ['success', 'message', 'data', 'errors'];
    }

    // ——— Acceso ———

    public function test_favorites_require_authentication(): void
    {
        $producto = Product::factory()->create();

        $this->getJson('/api/favoritos')->assertStatus(401);
        $this->postJson('/api/favoritos', ['product_id' => $producto->id])->assertStatus(401);
        $this->deleteJson("/api/favoritos/{$producto->id}")->assertStatus(401);
    }

    // ——— Listado ———

    public function test_empty_list_uses_the_standard_envelope(): void
    {
        $this->withHeaders($this->headers)->getJson('/api/favoritos')
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['favorites', 'count'], 'errors'])
            ->assertJson(['success' => true, 'errors' => null, 'data' => ['favorites' => [], 'count' => 0]]);
    }

    public function test_list_returns_only_my_favorites_with_product_and_category(): void
    {
        $mio = Product::factory()->create();
        $ajeno = Product::factory()->create();
        Favorite::factory()->create(['user_id' => $this->user->id, 'product_id' => $mio->id]);
        Favorite::factory()->create(['product_id' => $ajeno->id]); // de otro usuario

        $response = $this->withHeaders($this->headers)->getJson('/api/favoritos')->assertStatus(200);

        $this->assertSame(1, $response->json('data.count'));
        $this->assertSame($mio->id, $response->json('data.favorites.0.product.id'));
        $this->assertNotNull($response->json('data.favorites.0.product.category.name'));
    }

    // ——— Añadir ———

    public function test_add_favorite(): void
    {
        $producto = Product::factory()->create();

        $this->withHeaders($this->headers)->postJson('/api/favoritos', ['product_id' => $producto->id])
            ->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data' => ['favorite' => ['id', 'product_id']], 'errors'])
            ->assertJsonPath('data.favorite.product_id', $producto->id)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('favorites', ['user_id' => $this->user->id, 'product_id' => $producto->id]);
    }

    public function test_adding_a_duplicate_favorite_returns_409(): void
    {
        $producto = Product::factory()->create();
        Favorite::factory()->create(['user_id' => $this->user->id, 'product_id' => $producto->id]);

        $this->withHeaders($this->headers)->postJson('/api/favoritos', ['product_id' => $producto->id])
            ->assertStatus(409)
            ->assertJsonStructure($this->envelope())
            ->assertJson(['success' => false, 'data' => null]);

        $this->assertSame(1, Favorite::where('user_id', $this->user->id)->count());
    }

    public function test_add_favorite_validation_uses_the_standard_envelope(): void
    {
        $this->withHeaders($this->headers)->postJson('/api/favoritos', [])
            ->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'data', 'errors' => ['product_id']])
            ->assertJson(['success' => false]);

        $this->withHeaders($this->headers)->postJson('/api/favoritos', ['product_id' => 999999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('product_id');
    }

    // ——— Quitar ———

    public function test_remove_favorite(): void
    {
        $producto = Product::factory()->create();
        Favorite::factory()->create(['user_id' => $this->user->id, 'product_id' => $producto->id]);

        $this->withHeaders($this->headers)->deleteJson("/api/favoritos/{$producto->id}")
            ->assertStatus(200)
            ->assertJsonStructure($this->envelope())
            ->assertJson(['success' => true, 'data' => null]);

        $this->assertDatabaseMissing('favorites', ['user_id' => $this->user->id, 'product_id' => $producto->id]);
    }

    public function test_removing_a_non_favorite_returns_404(): void
    {
        $producto = Product::factory()->create();

        $this->withHeaders($this->headers)->deleteJson("/api/favoritos/{$producto->id}")
            ->assertStatus(404)
            ->assertJsonStructure($this->envelope())
            ->assertJson(['success' => false]);
    }

    public function test_cannot_remove_another_users_favorite(): void
    {
        $ajeno = Favorite::factory()->create();

        $this->withHeaders($this->headers)->deleteJson("/api/favoritos/{$ajeno->product_id}")->assertStatus(404);

        $this->assertDatabaseHas('favorites', ['id' => $ajeno->id]);
    }
}
