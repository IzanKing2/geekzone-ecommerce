<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías
        $marvel = Category::where('name', 'Marvel')->first();
        $kpop = Category::where('name', 'K-Pop')->first();
        $futbol = Category::where('name', 'Fútbol')->first();

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS MARVEL
        // ——————————————————————————————————————————————————————————————————————
        $MarvelProducts = [
            [
                'name' => 'Neón inspiración MARVEL',
                'description' => 'Siéntete cómo un verdadero súper héroe con este fantástico neón Marvel con todos sus personajes impresos en metacrilato.',
                'price' => 167.48,
                'stock' => 25,
                'featured' => false,
                'image_url' => 'img/products/marvel/01.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Lámpara de noche Spider-Man',
                'description' => 'Lámpara de noche de superhéroe Spider-Man Lámpara de epoxi hecha a mano de madera y resina Luz de noche personalizada Lámpara de madera Lámpara de resina Lámpara de resina Regalo para él',
                'price' => 29.10,
                'stock' => 10,
                'featured' => false,
                'image_url' => 'img/products/marvel/02.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Taza Comic',
                'description' => 'Taza de cerámica de 330ml con dibujos de comics Marvel. Apta para microondas y lavavajillas.',
                'price' => 12.99,
                'stock' => 65,
                'featured' => false,
                'image_url' => 'img/products/marvel/03.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Hot Toys Spider-Man (Traje Mejorado)',
                'description' => 'En Spider-Man: Far From Home, Peter Parker planea dejar atrás súper heroicos durante unas semanas con sus amigos para unas vacaciones en Europa, pero varios ataques de criatura están plagando el continente.',
                'price' => 413.46,
                'stock' => 126,
                'featured' => true,
                'image_url' => 'img/products/marvel/04.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Venom - Figura coleccionable',
                'description' => 'La figura coleccionable muy detallada está diseñada por expertos para representar a Venom de Marvel Comics. Cuenta con una escultura de cabeza sorprendentemente sonriente, así como una cabeza con una boca amenazante con colmillos y tres lenguas sobresalientes intercambiables.',
                'price' => 589,
                'stock' => 1,
                'featured' => false,
                'image_url' => 'img/products/marvel/05.jpg',
                'category_id' => $marvel->id,
            ],
        ];

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS STRAY KIDS
        // ——————————————————————————————————————————————————————————————————————
        $kpopProducts = [
            [
                'name' => 'Álbum 5-STAR',
                'description' => 'The 3rd Album de Stray Kids. Incluye CD, photobook de lujo, photocards exclusivas y stickers. Disponible en versiones A, B y C.',
                'price' => 28.99,
                'stock' => 35,
                'featured' => false,
                'image_url' => 'img/products/kpop/5-star.jpg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'Mini Álbum ATE',
                'description' => 'Mini álbum ATE de Stray Kids. Edición especial con diseño holográfico. Incluye photobook, CD y set de photocards aleatorias.',
                'price' => 24.50,
                'stock' => 42,
                'featured' => false,
                'image_url' => 'img/products/kpop/ate.jpg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'SKZ IT TAPE "DO IT"',
                'description' => 'Edición especial IT VER. Incluye contenido exclusivo de la serie IT TAPE, con estética rosa y gris característica.',
                'price' => 19.99,
                'stock' => 20,
                'featured' => false,
                'image_url' => 'img/products/kpop/do-it.jpg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'Álbum KARMA (Accordion Ver.)',
                'description' => 'The 4th Album de Stray Kids en versión Accordion. Portadas individuales de los miembros, incluye booklet plegable y photocard.',
                'price' => 18.50,
                'stock' => 60,
                'featured' => true,
                'image_url' => 'img/products/kpop/karma.jpg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'Official Lightstick Nachimbong V2',
                'description' => 'Lightstick oficial de Stray Kids (Versión 2). Con conexión Bluetooth para conciertos y brújula giratoria interna.',
                'price' => 95.00,
                'stock' => 15,
                'featured' => false,
                'image_url' => 'img/products/kpop/lightstick.jpeg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'Christmas EveL (Standard Ver.)',
                'description' => 'Holiday Special Single de Stray Kids. Incluye CD, photobook, photocards aleatorias y un set de stickers navideños exclusivos.',
                'price' => 19.50,
                'stock' => 25,
                'featured' => false,
                'image_url' => 'img/products/kpop/chiristmas-evel.jpg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'Pre-Debut Album "Mixtape"',
                'description' => 'Álbum debut de Stray Kids. Incluye photobook de 176 páginas, CD-R y 2 tipos de photocards (Making ver. y Selfie ver.).',
                'price' => 21.00,
                'stock' => 15,
                'featured' => false,
                'image_url' => 'img/products/kpop/mixtape.jpg',
                'category_id' => $kpop->id,
            ],
            [
                'name' => 'Mini Álbum ODDINARY (Frankenstein Ver.)',
                'description' => 'Edición limitada Frankenstein. Incluye caja de empaque, photobook, CD, lyric paper, ID photocard, mini póster y contenido exclusivo de preventa.',
                'price' => 29.99,
                'stock' => 10,
                'featured' => false,
                'image_url' => 'img/products/kpop/oddinary.jpg',
                'category_id' => $kpop->id,
            ],
        ];

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS FÚTBOL
        // ——————————————————————————————————————————————————————————————————————
        $FutbolProducts = [
            [
                'name' => 'Balón Trionda FIFA World Cup 2026',
                'description' => 'Balón de alta calidad, termosellado y textura optimizada. Diseño inspirado en la unión de los tres países anfitriones del Mundial 2026.',
                'price' => 149.99,
                'stock' => 50,
                'featured' => true,
                'image_url' => 'img/products/futbol/balon-trionda-2026.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Caja de Cromos Adrenalyn XL FIFA World Cup 2026',
                'description' => 'Colección oficial con más de 630 cartas. Incluye gráficos impresionantes, materiales especiales y los mejores jugadores del mundo.',
                'price' => 80.00,
                'stock' => 100,
                'featured' => false,
                'image_url' => 'img/products/futbol/cromos-adrenalyn-2026.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Camiseta Adidas Aeroready Real Madrid 25/26',
                'description' => 'Camiseta oficial idéntica a la de los jugadores. Tejido transpirable de poliéster con tecnología Aeroready para máximo rendimiento.',
                'price' => 99.95,
                'stock' => 40,
                'featured' => false,
                'image_url' => 'img/products/futbol/camiseta-real-madrid-2526.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Funko Pop Barcelona Lamine Yamal',
                'description' => 'Figura coleccionable de la joven estrella del FC Barcelona. Detalles fieles a su imagen deportiva y estilo inconfundible de Funko.',
                'price' => 15.99,
                'stock' => 30,
                'featured' => false,
                'image_url' => 'img/products/futbol/funko-lamine-yamal.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Funko Pop Inter de Miami Leo Messi',
                'description' => 'Figura homenaje al legado de Lionel Messi. Captura la esencia del astro argentino en su etapa actual, ideal para coleccionistas.',
                'price' => 15.99,
                'stock' => 60,
                'featured' => false,
                'image_url' => 'img/products/futbol/funko-messi-miami.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Mini Réplica Trofeo Champions League',
                'description' => 'Reproducción detallada del trofeo de clubes más deseado del mundo. Captura la precisión y el brillo de la "Orejona" original.',
                'price' => 34.50,
                'stock' => 20,
                'featured' => false,
                'image_url' => 'img/products/futbol/replica-champions.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Peluche Eduardo Camavinga',
                'description' => 'Peluche suave y detallado inspirado en el carismático jugador del Real Madrid. Ideal para fans de todas las edades.',
                'price' => 24.99,
                'stock' => 15,
                'featured' => false,
                'image_url' => 'img/products/futbol/peluche-camavinga.png',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Set de Minibalones Mundiales Adidas',
                'description' => 'Edición limitada que reúne las réplicas de los balones más icónicos de la historia de los Mundiales de la FIFA.',
                'price' => 65.00,
                'stock' => 10,
                'featured' => false,
                'image_url' => 'img/products/futbol/set-minibalones-adidas.png',
                'category_id' => $futbol->id,
            ],
        ];

        foreach (array_merge($MarvelProducts, $kpopProducts, $FutbolProducts) as $producto) {
            Product::create($producto);
        }
    }
}
