<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartOrderRobustnessTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    private array $headers;

    private User $user;

    /** [0] = 29.99 € (stock 10), [1] = 19.99 € (stock 5), [2] = 12.50 € (stock 20) */
    private array $productos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headers = $this->loginAsUser();
        $this->user = User::where('email', 'test@geekzone.com')->first();
        [$marvel] = $this->createCategoriesForTest();
        $this->productos = $this->createProductsForTest($marvel->id);
    }

    private function anadir(Product $producto, int $cantidad = 1)
    {
        return $this->withHeaders($this->headers)->postJson('/api/carrito', [
            'product_id' => $producto->id,
            'quantity' => $cantidad,
        ]);
    }

    private function pedir()
    {
        return $this->withHeaders($this->headers)->postJson('/api/pedidos');
    }

    // ——— Carrito ———

    public function test_cart_excludes_deleted_products_instead_of_failing(): void
    {
        $this->anadir($this->productos[0])->assertStatus(201);
        $this->anadir($this->productos[1])->assertStatus(201);
        $this->productos[0]->delete();

        $response = $this->withHeaders($this->headers)->getJson('/api/carrito')->assertStatus(200);

        $this->assertSame(1, $response->json('items_count'));
        $this->assertEquals(19.99, $response->json('total'));
    }

    public function test_cannot_add_deleted_product_to_cart(): void
    {
        $this->productos[0]->delete();

        $this->anadir($this->productos[0])->assertStatus(422)->assertJsonValidationErrors('product_id');
    }

    public function test_cart_stock_check_uses_accumulated_quantity(): void
    {
        $this->anadir($this->productos[0], 6)->assertStatus(201);
        $this->anadir($this->productos[0], 6)->assertStatus(400); // 6 + 6 > stock 10

        $this->assertSame(6, Cart::where('user_id', $this->user->id)->value('quantity'));
        $this->anadir($this->productos[0], 4)->assertStatus(201); // 6 + 4 = 10, justo el stock
        $this->assertSame(10, Cart::where('user_id', $this->user->id)->value('quantity'));
    }

    public function test_adding_same_product_twice_keeps_a_single_cart_row(): void
    {
        $this->anadir($this->productos[2], 2)->assertStatus(201);
        $this->anadir($this->productos[2], 3)->assertStatus(201);

        $this->assertSame(1, Cart::where('user_id', $this->user->id)->count());
        $this->assertSame(5, Cart::where('user_id', $this->user->id)->value('quantity'));
    }

    public function test_cart_quantity_has_an_upper_limit(): void
    {
        $this->anadir($this->productos[2], 100)->assertStatus(422)->assertJsonValidationErrors('quantity');

        $item = Cart::create(['user_id' => $this->user->id, 'product_id' => $this->productos[2]->id, 'quantity' => 1]);
        $this->withHeaders($this->headers)->putJson("/api/carrito/{$item->id}", ['quantity' => 100])
            ->assertStatus(422)->assertJsonValidationErrors('quantity');
    }

    public function test_updating_item_of_deleted_product_returns_404_and_removes_it(): void
    {
        $this->anadir($this->productos[0])->assertStatus(201);
        $item = Cart::where('user_id', $this->user->id)->first();
        $this->productos[0]->delete();

        $this->withHeaders($this->headers)->putJson("/api/carrito/{$item->id}", ['quantity' => 2])
            ->assertStatus(404);

        $this->assertDatabaseMissing('carts', ['id' => $item->id]);
    }

    public function test_user_cannot_modify_another_users_cart_item(): void
    {
        $otro = User::create([
            'name' => 'Otro', 'surname' => 'Usuario', 'username' => 'otro',
            'email' => 'otro@geekzone.com', 'password' => 'password123', 'role' => 'user',
        ]);
        $ajeno = Cart::create(['user_id' => $otro->id, 'product_id' => $this->productos[0]->id, 'quantity' => 1]);

        $this->withHeaders($this->headers)->putJson("/api/carrito/{$ajeno->id}", ['quantity' => 3])->assertStatus(404);
        $this->withHeaders($this->headers)->deleteJson("/api/carrito/{$ajeno->id}")->assertStatus(404);

        $this->assertSame(1, $ajeno->fresh()->quantity);
    }

    // ——— Favoritos ———

    public function test_cannot_favorite_a_deleted_product(): void
    {
        $this->productos[0]->delete();

        $this->withHeaders($this->headers)->postJson('/api/favoritos', ['product_id' => $this->productos[0]->id])
            ->assertStatus(422);
    }

    public function test_favorites_exclude_deleted_products(): void
    {
        Favorite::create(['user_id' => $this->user->id, 'product_id' => $this->productos[0]->id]);
        Favorite::create(['user_id' => $this->user->id, 'product_id' => $this->productos[1]->id]);
        $this->productos[0]->delete();

        $response = $this->withHeaders($this->headers)->getJson('/api/favoritos')->assertStatus(200);

        $this->assertSame(1, $response->json('data.count'));
        $this->assertSame($this->productos[1]->id, $response->json('data.favorites.0.product.id'));
    }

    // ——— Pedidos ———

    public function test_order_computes_exact_total_decrements_stock_and_empties_cart(): void
    {
        $this->anadir($this->productos[0], 2)->assertStatus(201); // 59.98
        $this->anadir($this->productos[1], 1)->assertStatus(201); // 19.99

        $response = $this->pedir()->assertStatus(201);

        $this->assertEquals(79.97, $response->json('data.order.total'));
        $this->assertSame('pendiente', $response->json('data.order.status'));
        $this->assertSame(8, $this->productos[0]->fresh()->stock);
        $this->assertSame(4, $this->productos[1]->fresh()->stock);
        $this->assertSame(0, Cart::where('user_id', $this->user->id)->count());
        $this->assertSame(2, OrderDetail::where('order_id', $response->json('data.order.id'))->count());
    }

    public function test_order_total_has_no_floating_point_drift(): void
    {
        // 3 x 0.10 en coma flotante da 0.30000000000000004: el total debe salir exacto
        $this->productos[2]->update(['price' => 0.10, 'stock' => 50]);
        $this->anadir($this->productos[2], 3)->assertStatus(201);

        $this->assertEquals(0.30, $this->pedir()->assertStatus(201)->json('data.order.total'));
    }

    public function test_second_submit_does_not_create_a_second_order(): void
    {
        $this->anadir($this->productos[0], 1)->assertStatus(201);

        $this->pedir()->assertStatus(201);
        $this->pedir()->assertStatus(404); // el carrito ya está vacío (doble clic)

        $this->assertSame(1, Order::where('user_id', $this->user->id)->count());
        $this->assertSame(9, $this->productos[0]->fresh()->stock); // descontado una sola vez
    }

    public function test_order_fails_atomically_when_stock_is_not_enough(): void
    {
        $this->anadir($this->productos[0], 2)->assertStatus(201);
        $this->anadir($this->productos[1], 3)->assertStatus(201);
        // Otro cliente compra y deja sin stock el segundo producto
        $this->productos[1]->update(['stock' => 1]);

        $this->pedir()->assertStatus(400);

        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderDetail::count());
        $this->assertSame(10, $this->productos[0]->fresh()->stock); // nada se descontó
        $this->assertSame(2, Cart::where('user_id', $this->user->id)->count()); // el carrito sigue intacto
    }

    public function test_order_ignores_deleted_products_in_cart(): void
    {
        $this->anadir($this->productos[0], 1)->assertStatus(201);
        $this->anadir($this->productos[1], 1)->assertStatus(201);
        $this->productos[0]->delete();

        $response = $this->pedir()->assertStatus(201);

        $this->assertEquals(19.99, $response->json('data.order.total'));
        $this->assertSame(1, OrderDetail::where('order_id', $response->json('data.order.id'))->count());
        $this->assertSame(0, Cart::where('user_id', $this->user->id)->count());
    }

    public function test_order_with_only_deleted_products_behaves_as_empty_cart(): void
    {
        $this->anadir($this->productos[0], 1)->assertStatus(201);
        $this->productos[0]->delete();

        $this->pedir()->assertStatus(404);

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Cart::where('user_id', $this->user->id)->count());
    }

    public function test_order_history_keeps_deleted_products(): void
    {
        $this->anadir($this->productos[0], 1)->assertStatus(201);
        $this->pedir()->assertStatus(201);
        $this->productos[0]->delete();

        $response = $this->withHeaders($this->headers)->getJson('/api/pedidos')->assertStatus(200);

        $this->assertSame('Figura Spider-Man Test', $response->json('data.orders.0.details.0.product.name'));
    }
}
