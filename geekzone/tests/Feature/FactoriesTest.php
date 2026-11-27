<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Comprueba que las factories siguen siendo compatibles con el esquema y los modelos
 * (si una migración cambia, este test avisa antes de que fallen decenas de tests).
 */
class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_creates_a_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertSame('user', $user->role);
        $this->assertNotEmpty($user->username);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertFalse($user->esAdmin());
    }

    public function test_user_factory_admin_state(): void
    {
        $this->assertTrue(User::factory()->admin()->create()->esAdmin());
    }

    public function test_user_factory_generates_unique_values(): void
    {
        $users = User::factory()->count(5)->create();

        $this->assertCount(5, $users->pluck('email')->unique());
        $this->assertCount(5, $users->pluck('username')->unique());
    }

    public function test_product_factory_creates_its_category(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertSame(10, $product->stock);
    }

    public function test_product_factory_states(): void
    {
        $this->assertSame(0, Product::factory()->outOfStock()->create()->stock);
        $this->assertTrue((bool) Product::factory()->featured()->create()->featured);
    }

    public function test_cart_and_favorite_factories(): void
    {
        $cart = Cart::factory()->create(['quantity' => 3]);
        $favorite = Favorite::factory()->create();

        $this->assertSame(3, $cart->quantity);
        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertInstanceOf(Product::class, $favorite->product);
    }

    public function test_order_and_order_detail_factories(): void
    {
        $order = Order::factory()->status('entregado')->create();
        $detail = OrderDetail::factory()->create(['order_id' => $order->id]);

        $this->assertSame('entregado', $order->status);
        $this->assertSame($order->id, $detail->order->id);
        $this->assertInstanceOf(Product::class, $detail->product);
    }
}
