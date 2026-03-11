<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Marvel',
                'descripcion' => 'Productos oficiales del universo Marvel: figuras de acción, cómics, camisetas, tazas y más de tus superhéroes favoritos como Spider-Man, Iron Man, Thor y los Vengadores.',
                'imagen_url' => 'img/categorias/marvel.jpg',
            ],
            [
                'nombre' => 'Stray Kids',
                'descripcion' => 'Merchandising oficial de Stray Kids: álbumes, lightsticks, photocards, camisetas y accesorios del grupo de K-pop más innovador del momento.',
                'imagen_url' => 'img/categorias/straykids.jpg',
            ],
            [
                'nombre' => 'Fútbol',
                'descripcion' => 'Equipamiento y merchandising de fútbol: camisetas oficiales, balones, bufandas, figuras de jugadores y accesorios de los mejores equipos del mundo.',
                'imagen_url' => 'img/categorias/futbol.jpg',
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
