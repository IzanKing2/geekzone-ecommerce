<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    private User $cliente;

    private array $productos;

    private const ENDPOINTS = [
        'resumen',
        'ingresos',
        'top-productos',
        'ventas-por-categoria',
        'pedidos-recientes',
        'pedidos-por-cliente',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->cliente = $this->createNormalUser();
        [$marvel] = $this->createCategoriesForTest();
        // [0] = 29.99 €, [1] = 19.99 €, [2] = 12.50 €
        $this->productos = $this->createProductsForTest($marvel->id);
    }

    /** @param array<int, array{0: Product, 1: int}> $lineas */
    private function crearPedido(string $estado, array $lineas): Order
    {
        $total = 0;
        foreach ($lineas as [$producto, $cantidad]) {
            $total += $producto->price * $cantidad;
        }

        $pedido = Order::create(['user_id' => $this->cliente->id, 'status' => $estado, 'total' => $total]);

        foreach ($lineas as [$producto, $cantidad]) {
            OrderDetail::create([
                'order_id' => $pedido->id,
                'product_id' => $producto->id,
                'quantity' => $cantidad,
                'price' => $producto->price,
            ]);
        }

        return $pedido;
    }

    /** Pedido entregado (79.97 €) + pedido cancelado que NO debe contar. */
    private function datosDeVentas(): void
    {
        [$p1, $p2] = $this->productos;
        $this->crearPedido('entregado', [[$p1, 2], [$p2, 1]]);
        $this->crearPedido('cancelado', [[$p1, 10]]);
    }

    // ——— Acceso ———

    public function test_all_dashboard_endpoints_require_authentication(): void
    {
        foreach (self::ENDPOINTS as $endpoint) {
            $this->getJson("/api/admin/dashboard/$endpoint")->assertStatus(401);
        }
    }

    public function test_all_dashboard_endpoints_forbid_normal_users(): void
    {
        // El usuario normal ya existe (setUp): solo hay que iniciar sesión con él
        $headers = $this->getHeadersWithToken($this->getTokenForUser('test@geekzone.com', 'password123'));

        foreach (self::ENDPOINTS as $endpoint) {
            $this->getJson("/api/admin/dashboard/$endpoint", $headers)->assertStatus(403);
        }
    }

    public function test_all_dashboard_endpoints_work_for_admin_without_data(): void
    {
        $headers = $this->loginAsAdmin();

        foreach (self::ENDPOINTS as $endpoint) {
            $this->getJson("/api/admin/dashboard/$endpoint", $headers)
                ->assertStatus(200)
                ->assertJson(['success' => true]);
        }
    }

    // ——— Resultados ———

    public function test_resumen_excludes_cancelled_orders_from_revenue(): void
    {
        $this->datosDeVentas();
        $headers = $this->loginAsAdmin();

        $this->getJson('/api/admin/dashboard/resumen', $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.total_pedidos', 2)
            ->assertJsonPath('data.total_usuarios', 2)
            ->assertJsonPath('data.ingresos_totales', 79.97);
    }

    public function test_ingresos_groups_by_month_and_excludes_cancelled(): void
    {
        $this->datosDeVentas();
        $headers = $this->loginAsAdmin();

        $response = $this->getJson('/api/admin/dashboard/ingresos', $headers)->assertStatus(200);

        $this->assertCount(1, $response->json('data.ingresos_mensuales'));
        $this->assertSame(now()->format('Y-m'), $response->json('data.ingresos_mensuales.0.mes'));
        $this->assertEquals(79.97, $response->json('data.ingresos_mensuales.0.ingresos'));
    }

    public function test_ingresos_respects_months_window(): void
    {
        $this->datosDeVentas();
        $antiguo = $this->crearPedido('entregado', [[$this->productos[2], 1]]);
        $antiguo->forceFill(['created_at' => now()->subMonths(5)])->save();
        $headers = $this->loginAsAdmin();

        $this->assertCount(2, $this->getJson('/api/admin/dashboard/ingresos?meses=6', $headers)->json('data.ingresos_mensuales'));
        $this->assertCount(1, $this->getJson('/api/admin/dashboard/ingresos?meses=1', $headers)->json('data.ingresos_mensuales'));
        // Valores fuera de rango se acotan (no dan error)
        $this->getJson('/api/admin/dashboard/ingresos?meses=999', $headers)->assertStatus(200);
        $this->getJson('/api/admin/dashboard/ingresos?meses=abc', $headers)->assertStatus(200);
    }

    public function test_top_productos_excludes_cancelled_and_keeps_deleted_products(): void
    {
        $this->datosDeVentas();
        $this->productos[0]->delete(); // soft delete
        $headers = $this->loginAsAdmin();

        $top = $this->getJson('/api/admin/dashboard/top-productos', $headers)
            ->assertStatus(200)
            ->json('data.top_productos');

        $this->assertCount(2, $top);
        $this->assertSame(2, (int) $top[0]['total_vendido']); // los 10 cancelados no cuentan
        $this->assertSame('Figura Spider-Man Test', $top[0]['product']['name']);
    }

    public function test_ventas_por_categoria_excludes_cancelled(): void
    {
        $this->datosDeVentas();
        $headers = $this->loginAsAdmin();

        $ventas = $this->getJson('/api/admin/dashboard/ventas-por-categoria', $headers)
            ->assertStatus(200)
            ->json('data.ventas_por_categoria');

        $this->assertCount(1, $ventas);
        $this->assertSame('Marvel', $ventas[0]['name']);
        $this->assertEquals(79.97, $ventas[0]['total_ventas']);
    }

    public function test_pedidos_recientes_returns_latest_eight_with_customer(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->crearPedido('pendiente', [[$this->productos[2], 1]]);
        }
        $headers = $this->loginAsAdmin();

        $pedidos = $this->getJson('/api/admin/dashboard/pedidos-recientes', $headers)
            ->assertStatus(200)
            ->json('data.pedidos_recientes');

        $this->assertCount(8, $pedidos);
        $this->assertSame('test@geekzone.com', $pedidos[0]['user']['email']);
        $this->assertGreaterThan($pedidos[7]['id'], $pedidos[0]['id']); // más recientes primero
    }

    public function test_pedidos_por_cliente_ranks_customers(): void
    {
        $this->datosDeVentas();
        $headers = $this->loginAsAdmin();

        $this->getJson('/api/admin/dashboard/pedidos-por-cliente', $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.pedidos_por_cliente.0.user.email', 'test@geekzone.com')
            ->assertJsonPath('data.pedidos_por_cliente.0.numero_pedidos', 2);
    }

    // ——— La página /admin ya no filtra datos sensibles ———

    public function test_admin_pages_do_not_render_sensitive_data_on_server(): void
    {
        $this->datosDeVentas();

        // Visitante anónimo: ni emails de clientes ni importes de pedidos en el HTML
        $this->get('/admin')
            ->assertOk()
            ->assertDontSee('test@geekzone.com')
            ->assertDontSee('79,97')
            ->assertDontSee('Usuario Test');

        $this->get('/admin/categorias')
            ->assertOk()
            ->assertDontSee('79');
    }
}
