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
        $strayKids = Category::where('name', 'Stray Kids')->first();
        $futbol = Category::where('name', 'Fútbol')->first();

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS MARVEL
        // ——————————————————————————————————————————————————————————————————————
        $MarvelProducts = [
            [
                'name' => 'Figura Spider-Man Titan Hero',
                'description' => 'Figura articulada de Spider-Man de 30cm de la serie Titan Hero de Hasbro. Detalles fieles al diseño del MCU.',
                'price' => 29.99,
                'stock' => 25,
                'image_url' => 'img/products/spiderman-titan.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Camiseta Iron Man Arc Reactor',
                'description' => 'Camiseta 100% algodón con diseño del Arc Reactor de Tony Stark. Disponible en tallas S-XXL.',
                'price' => 24.99,
                'stock' => 40,
                'image_url' => 'img/products/camiseta-ironman.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Taza Vengadores Logo',
                'description' => 'Taza de cerámica de 330ml con el logo de los Vengadores. Apta para microondas y lavavajillas.',
                'price' => 12.99,
                'stock' => 60,
                'image_url' => 'img/products/taza-vengadores.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Funko Pop Thor',
                'description' => 'Figura Funko Pop #482 de Thor con Stormbreaker. Edición Love and Thunder. Altura: 10cm.',
                'price' => 15.99,
                'stock' => 35,
                'image_url' => 'img/products/funko-thor.jpg',
                'category_id' => $marvel->id,
            ],
            [
                'name' => 'Réplica Escudo Capitán América',
                'description' => 'Réplica a escala 1:1 del escudo del Capitán América. Material: metal y pintura de alta calidad. Diámetro: 60cm.',
                'price' => 89.99,
                'stock' => 10,
                'image_url' => 'img/products/escudo-capitan.jpg',
                'category_id' => $marvel->id,
            ],
        ];

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS STRAY KIDS
        // ——————————————————————————————————————————————————————————————————————
        $StrayKidsProducts = [
            [
                'name' => 'Álbum MAXIDENT',
                'description' => 'Mini álbum MAXIDENT de Stray Kids. Incluye CD, photobook, photocards aleatorias y póster plegable.',
                'price' => 22.99,
                'stock' => 50,
                'image_url' => 'img/products/album-maxident.jpg',
                'category_id' => $strayKids->id,
            ],
            [
                'name' => 'Lightstick Oficial Nachimbong',
                'description' => 'Lightstick oficial de Stray Kids (Nachimbong). Conexión Bluetooth para sincronización en conciertos.',
                'price' => 49.99,
                'stock' => 20,
                'image_url' => 'img/products/lightstick-skz.jpg',
                'category_id' => $strayKids->id,
            ],
            [
                'name' => 'Set Photocards Coleccionables',
                'description' => 'Set de 8 photocards exclusivas de los miembros de Stray Kids. Edición limitada con acabado holográfico.',
                'price' => 14.99,
                'stock' => 45,
                'image_url' => 'img/products/photocards-skz.jpg',
                'category_id' => $strayKids->id,
            ],
            [
                'name' => 'Sudadera SKZ Logo',
                'description' => 'Sudadera con capucha y logo bordado de Stray Kids. Algodón orgánico, tallas S-XL. Color: negro.',
                'price' => 39.99,
                'stock' => 30,
                'image_url' => 'img/products/sudadera-skz.jpg',
                'category_id' => $strayKids->id,
            ],
            [
                'name' => 'Álbum 5-STAR',
                'description' => 'Tercer álbum completo 5-STAR de Stray Kids. Incluye 3 versiones: A, B y C con contenido diferente.',
                'price' => 27.99,
                'stock' => 40,
                'image_url' => 'img/products/album-5star.jpg',
                'category_id' => $strayKids->id,
            ],
        ];

        // ——————————————————————————————————————————————————————————————————————
        // PRODUCTOS FÚTBOL
        // ——————————————————————————————————————————————————————————————————————
        $FutbolProducts = [
            [
                'name' => 'Camiseta FC Barcelona 24/25',
                'description' => 'Primera equipación oficial del FC Barcelona temporada 2024/2025. Tecnología Dri-FIT. Tallas S-XXL.',
                'price' => 89.99,
                'stock' => 30,
                'image_url' => 'img/products/camiseta-barca.jpg',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Balón Adidas UCL Pro',
                'description' => 'Balón oficial de la UEFA Champions League. Certificación FIFA Quality Pro. Cosido térmicamente.',
                'price' => 44.99,
                'stock' => 25,
                'image_url' => 'img/products/balon-ucl.jpg',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Botas Nike Mercurial Vapor 15',
                'description' => 'Botas de fútbol Nike Mercurial Vapor 15 Elite FG. Placa de fibra de carbono. Para césped natural.',
                'price' => 129.99,
                'stock' => 15,
                'image_url' => 'img/products/botas-mercurial.jpg',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Bufanda Real Madrid',
                'description' => 'Bufanda oficial del Real Madrid CF. Doble cara con escudo bordado. Material: acrílico.',
                'price' => 19.99,
                'stock' => 55,
                'image_url' => 'img/products/bufanda-madrid.jpg',
                'category_id' => $futbol->id,
            ],
            [
                'name' => 'Figura Messi Funko Pop',
                'description' => 'Figura Funko Pop de Lionel Messi con la equipación del Inter Miami. Altura: 10cm.',
                'price' => 16.99,
                'stock' => 40,
                'image_url' => 'img/products/funko-messi.jpg',
                'category_id' => $futbol->id,
            ],
        ];

        foreach (array_merge($MarvelProducts, $StrayKidsProducts, $FutbolProducts) as $producto) {
            Product::create($producto);
        }
    }
}
