<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Marvel',
                'description' => 'Productos oficiales del universo Marvel: figuras de acción, cómics, camisetas, tazas y más de tus superhéroes favoritos como Spider-Man, Iron Man, Thor y los Vengadores.',
                'image_url' => 'img/categories/marvel.jpg',
            ],
            [
                'name' => 'K-Pop',
                'description' => 'Merchandising oficial de K-Pop: álbumes, lightsticks, photocards, camisetas y accesorios del grupo de K-pop más innovador del momento.',
                'image_url' => 'img/categories/kpop.jpg',
            ],
            [
                'name' => 'Fútbol',
                'description' => 'Equipamiento y merchandising de fútbol: camisetas oficiales, balones, bufandas, figuras de jugadores y accesorios de los mejores equipos del mundo.',
                'image_url' => 'img/categories/futbol.jpg',
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
