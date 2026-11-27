<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Las vistas no deben llevar manejadores inline (onclick=, onchange=, onerror=…): una política
 * CSP con nonce los bloquea, y son además el vector típico de XSS. La interactividad se declara con
 * atributos data-* y public/js/utils.js la ejecuta por delegación de eventos.
 */
class InlineHandlersTest extends TestCase
{
    use RefreshDatabase;

    private const MANEJADOR_INLINE = '/\son(click|dblclick|change|input|error|submit|load|mouse\w+|key\w+|focus|blur)\s*=/i';

    /** @return array<string, array{string}> */
    public static function paginas(): array
    {
        $rutas = [
            '/', '/catalogo', '/login', '/register', '/cart', '/panel', '/favoritos',
            '/admin', '/admin/productos', '/admin/categorias', '/producto/1',
        ];

        return array_combine($rutas, array_map(fn ($ruta) => [$ruta], $rutas));
    }

    #[DataProvider('paginas')]
    public function test_page_renders_no_inline_event_handlers(string $ruta): void
    {
        Product::factory()->create(); // id 1, para /producto/1

        $html = $this->get($ruta)->assertOk()->getContent();

        $this->assertSame(0, preg_match(self::MANEJADOR_INLINE, $html), "La página $ruta contiene un manejador inline.");
    }

    public function test_hostile_names_stay_escaped_inside_data_args(): void
    {
        $categoria = Category::factory()->create(['name' => 'Cat "><img src=x onerror=alert(1)>']);
        Product::factory()->create([
            'name' => "Prod \"><script>alert(1)</script> con 'comillas'",
            'description' => "Línea 1\nLínea 2 </script>",
            'category_id' => $categoria->id,
        ]);

        foreach (['/admin/productos', '/admin/categorias'] as $ruta) {
            $html = $this->get($ruta)->assertOk()->getContent();

            // Ni etiquetas ni atributos inyectados: las comillas y < > llegan como entidades HTML
            $this->assertStringNotContainsString('<img src=x onerror', $html);
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
            // El payload sí aparece, pero como texto escapado (inofensivo)
            $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        }
    }
}
