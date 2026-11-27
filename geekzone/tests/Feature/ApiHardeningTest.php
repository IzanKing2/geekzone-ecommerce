<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ApiHardeningTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    // ——— Rutas y respuestas JSON ———

    public function test_non_numeric_ids_return_404_instead_of_500(): void
    {
        $headers = $this->loginAsAdmin();

        $this->getJson('/api/productos/abc')->assertStatus(404);
        $this->withHeaders($headers)->putJson('/api/productos/abc', ['name' => 'X'])->assertStatus(404);
        $this->withHeaders($headers)->deleteJson('/api/productos/abc')->assertStatus(404);
        $this->withHeaders($headers)->putJson('/api/categorias/abc', ['name' => 'X'])->assertStatus(404);
        $this->withHeaders($headers)->deleteJson('/api/carrito/abc')->assertStatus(404);
    }

    public function test_unknown_api_route_returns_json_404(): void
    {
        $this->get('/api/no-existe')
            ->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson(['success' => false, 'message' => 'Recurso no encontrado.', 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    public function test_wrong_http_method_returns_standard_405(): void
    {
        $this->getJson('/api/login')
            ->assertStatus(405)
            ->assertJson(['success' => false, 'message' => 'Método no permitido.', 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    public function test_unexpected_errors_do_not_leak_details_when_not_in_debug(): void
    {
        config(['app.debug' => false]);
        Route::get('/api/_prueba-error', fn () => throw new RuntimeException('detalle-interno-secreto'));

        $response = $this->getJson('/api/_prueba-error')
            ->assertStatus(500)
            ->assertJson(['success' => false, 'message' => 'Error interno del servidor.', 'data' => null]);

        $this->assertStringNotContainsString('detalle-interno-secreto', $response->getContent());
    }

    public function test_validation_errors_are_json_even_without_accept_header(): void
    {
        $headers = $this->loginAsUser();

        // Sin "Accept: application/json" Laravel redirigiría (302); la API debe dar 422 JSON
        $this->post('/api/favoritos', ['product_id' => 999999], $headers)
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonValidationErrors('product_id');
    }

    // ——— Límites de peticiones ———

    public function test_general_api_rate_limit(): void
    {
        for ($i = 0; $i < 120; $i++) {
            $this->getJson('/api/categorias')->assertStatus(200);
        }

        $this->getJson('/api/categorias')->assertStatus(429);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', ['email' => 'nadie@geekzone.com', 'password' => 'incorrecta'])->assertStatus(401);
        }

        $this->postJson('/api/login', ['email' => 'nadie@geekzone.com', 'password' => 'incorrecta'])
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    public function test_register_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/register', [])->assertStatus(422);
        }

        $this->postJson('/api/register', [])->assertStatus(429);
    }

    // ——— CORS ———

    public function test_cors_only_allows_configured_origins(): void
    {
        config(['cors.allowed_origins' => ['http://localhost:8080']]);

        $permitido = $this->call('OPTIONS', '/api/productos', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:8080',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);
        $this->assertSame('http://localhost:8080', $permitido->headers->get('Access-Control-Allow-Origin'));

        // Con un único origen configurado la cabecera siempre lleva ese origen: el navegador
        // bloquea al sitio ajeno porque no coincide. Lo esencial: nunca "*" ni el origen ajeno.
        $ajeno = $this->call('OPTIONS', '/api/productos', [], [], [], [
            'HTTP_ORIGIN' => 'https://sitio-malicioso.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);
        $this->assertNotSame('*', $ajeno->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('https://sitio-malicioso.example', $ajeno->headers->get('Access-Control-Allow-Origin'));
    }

    // ——— Productos: solo campos validados ———

    private ?int $categoriaId = null;

    private function datosProducto(array $extra = []): array
    {
        // La categoría se crea una sola vez por test (los nombres son únicos)
        $this->categoriaId ??= $this->createCategoriesForTest()[0]->id;

        return array_merge([
            'name' => 'Producto Nuevo',
            'description' => 'Descripción',
            'price' => 15.50,
            'stock' => 3,
            'category_id' => $this->categoriaId,
        ], $extra);
    }

    public function test_product_creation_ignores_non_validated_fields(): void
    {
        $headers = $this->loginAsAdmin();

        $response = $this->withHeaders($headers)->postJson('/api/productos', $this->datosProducto([
            'id' => 9999,
            'deleted_at' => now()->toDateTimeString(),
            'created_at' => '2000-01-01 00:00:00',
        ]))->assertStatus(201);

        $producto = Product::find($response->json('data.product.id'));
        $this->assertNotSame(9999, $producto->id);
        $this->assertNull($producto->deleted_at);
        $this->assertNotSame('2000', $producto->created_at->format('Y'));
    }

    public function test_product_featured_is_validated_and_saved(): void
    {
        $headers = $this->loginAsAdmin();

        $this->withHeaders($headers)->postJson('/api/productos', $this->datosProducto(['featured' => 'quizás']))
            ->assertStatus(422)->assertJsonValidationErrors('featured');

        $response = $this->withHeaders($headers)->postJson('/api/productos', $this->datosProducto(['featured' => true]))
            ->assertStatus(201);

        $this->assertTrue((bool) Product::find($response->json('data.product.id'))->featured);
    }

    public function test_product_image_url_rejects_executable_schemes(): void
    {
        $headers = $this->loginAsAdmin();

        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'data:text/html;base64,AAAA', 'vbscript:x'] as $peligrosa) {
            $this->withHeaders($headers)->postJson('/api/productos', $this->datosProducto(['image_url' => $peligrosa]))
                ->assertStatus(422)->assertJsonValidationErrors('image_url');
        }

        foreach (['/storage/images/foto.jpg', 'https://cdn.example.com/foto.png', 'img/products/foto.jpg'] as $valida) {
            $this->withHeaders($headers)->postJson('/api/productos', $this->datosProducto(['image_url' => $valida]))
                ->assertStatus(201);
        }
    }

    public function test_product_update_ignores_non_validated_fields_and_validates_image(): void
    {
        $headers = $this->loginAsAdmin();
        [$marvel] = $this->createCategoriesForTest();
        [$producto] = $this->createProductsForTest($marvel->id);

        $this->withHeaders($headers)->putJson("/api/productos/{$producto->id}", [
            'name' => 'Renombrado',
            'id' => 12345,
            'deleted_at' => now()->toDateTimeString(),
        ])->assertStatus(200);

        $producto->refresh();
        $this->assertSame('Renombrado', $producto->name);
        $this->assertNull($producto->deleted_at);

        $this->withHeaders($headers)->putJson("/api/productos/{$producto->id}", ['image_url' => 'javascript:alert(1)'])
            ->assertStatus(422);
    }

    public function test_category_image_url_rejects_executable_schemes(): void
    {
        $headers = $this->loginAsAdmin();

        $this->withHeaders($headers)->postJson('/api/categorias', [
            'name' => 'Categoría X',
            'image_url' => 'javascript:alert(1)',
        ])->assertStatus(422);
    }
}
