<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías
        $marvel = Categoria::where('nombre', 'Marvel')->first();
        $strayKids = Categoria::where('nombre', 'Stray Kids')->first();
        $futbol = Categoria::where('nombre', 'Fútbol')->first();

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS MARVEL
        // ——————————————————————————————————————————————————————————————————————
        $productosMarvel = [
            [
                'nombre' => 'Figura Spider-Man Titan Hero',
                'descripcion' => 'Figura articulada de Spider-Man de 30cm de la serie Titan Hero de Hasbro. Detalles fieles al diseño del MCU.',
                'precio' => 29.99,
                'stock' => 25,
                'imagen_url' => 'img/productos/spiderman-titan.jpg',
                'categoria_id' => $marvel->id,
            ],
            [
                'nombre' => 'Camiseta Iron Man Arc Reactor',
                'descripcion' => 'Camiseta 100% algodón con diseño del Arc Reactor de Tony Stark. Disponible en tallas S-XXL.',
                'precio' => 24.99,
                'stock' => 40,
                'imagen_url' => 'img/productos/camiseta-ironman.jpg',
                'categoria_id' => $marvel->id,
            ],
            [
                'nombre' => 'Taza Vengadores Logo',
                'descripcion' => 'Taza de cerámica de 330ml con el logo de los Vengadores. Apta para microondas y lavavajillas.',
                'precio' => 12.99,
                'stock' => 60,
                'imagen_url' => 'img/productos/taza-vengadores.jpg',
                'categoria_id' => $marvel->id,
            ],
            [
                'nombre' => 'Funko Pop Thor',
                'descripcion' => 'Figura Funko Pop #482 de Thor con Stormbreaker. Edición Love and Thunder. Altura: 10cm.',
                'precio' => 15.99,
                'stock' => 35,
                'imagen_url' => 'img/productos/funko-thor.jpg',
                'categoria_id' => $marvel->id,
            ],
            [
                'nombre' => 'Réplica Escudo Capitán América',
                'descripcion' => 'Réplica a escala 1:1 del escudo del Capitán América. Material: metal y pintura de alta calidad. Diámetro: 60cm.',
                'precio' => 89.99,
                'stock' => 10,
                'imagen_url' => 'img/productos/escudo-capitan.jpg',
                'categoria_id' => $marvel->id,
            ],
        ];

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS STRAY KIDS
        // ——————————————————————————————————————————————————————————————————————
        $productosStrayKids = [
            [
                'nombre' => 'Álbum MAXIDENT',
                'descripcion' => 'Mini álbum MAXIDENT de Stray Kids. Incluye CD, photobook, photocards aleatorias y póster plegable.',
                'precio' => 22.99,
                'stock' => 50,
                'imagen_url' => 'img/productos/album-maxident.jpg',
                'categoria_id' => $strayKids->id,
            ],
            [
                'nombre' => 'Lightstick Oficial Nachimbong',
                'descripcion' => 'Lightstick oficial de Stray Kids (Nachimbong). Conexión Bluetooth para sincronización en conciertos.',
                'precio' => 49.99,
                'stock' => 20,
                'imagen_url' => 'img/productos/lightstick-skz.jpg',
                'categoria_id' => $strayKids->id,
            ],
            [
                'nombre' => 'Set Photocards Coleccionables',
                'descripcion' => 'Set de 8 photocards exclusivas de los miembros de Stray Kids. Edición limitada con acabado holográfico.',
                'precio' => 14.99,
                'stock' => 45,
                'imagen_url' => 'img/productos/photocards-skz.jpg',
                'categoria_id' => $strayKids->id,
            ],
            [
                'nombre' => 'Sudadera SKZ Logo',
                'descripcion' => 'Sudadera con capucha y logo bordado de Stray Kids. Algodón orgánico, tallas S-XL. Color: negro.',
                'precio' => 39.99,
                'stock' => 30,
                'imagen_url' => 'img/productos/sudadera-skz.jpg',
                'categoria_id' => $strayKids->id,
            ],
            [
                'nombre' => 'Álbum 5-STAR',
                'descripcion' => 'Tercer álbum completo 5-STAR de Stray Kids. Incluye 3 versiones: A, B y C con contenido diferente.',
                'precio' => 27.99,
                'stock' => 40,
                'imagen_url' => 'img/productos/album-5star.jpg',
                'categoria_id' => $strayKids->id,
            ],
        ];

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS FÚTBOL
        // ——————————————————————————————————————————————————————————————————————
        $productosFutbol = [
            [
                'nombre' => 'Camiseta FC Barcelona 24/25',
                'descripcion' => 'Primera equipación oficial del FC Barcelona temporada 2024/2025. Tecnología Dri-FIT. Tallas S-XXL.',
                'precio' => 89.99,
                'stock' => 30,
                'imagen_url' => 'img/productos/camiseta-barca.jpg',
                'categoria_id' => $futbol->id,
            ],
            [
                'nombre' => 'Balón Adidas UCL Pro',
                'descripcion' => 'Balón oficial de la UEFA Champions League. Certificación FIFA Quality Pro. Cosido térmicamente.',
                'precio' => 44.99,
                'stock' => 25,
                'imagen_url' => 'img/productos/balon-ucl.jpg',
                'categoria_id' => $futbol->id,
            ],
            [
                'nombre' => 'Botas Nike Mercurial Vapor 15',
                'descripcion' => 'Botas de fútbol Nike Mercurial Vapor 15 Elite FG. Placa de fibra de carbono. Para césped natural.',
                'precio' => 129.99,
                'stock' => 15,
                'imagen_url' => 'img/productos/botas-mercurial.jpg',
                'categoria_id' => $futbol->id,
            ],
            [
                'nombre' => 'Bufanda Real Madrid',
                'descripcion' => 'Bufanda oficial del Real Madrid CF. Doble cara con escudo bordado. Material: acrílico.',
                'precio' => 19.99,
                'stock' => 55,
                'imagen_url' => 'img/productos/bufanda-madrid.jpg',
                'categoria_id' => $futbol->id,
            ],
            [
                'nombre' => 'Figura Messi Funko Pop',
                'descripcion' => 'Figura Funko Pop de Lionel Messi con la equipación del Inter Miami. Altura: 10cm.',
                'precio' => 16.99,
                'stock' => 40,
                'imagen_url' => 'img/productos/funko-messi.jpg',
                'categoria_id' => $futbol->id,
            ],
        ];

        foreach (array_merge($productosMarvel, $productosStrayKids, $productosFutbol) as $producto) {
            Producto::create($producto);
        }
    }
}
