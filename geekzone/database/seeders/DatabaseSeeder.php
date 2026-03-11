<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeders...');
        $this->call([
            CategoriaSeeder::class,
            ProductoSeeder::class,
            UserSeeder::class,
        ]);
        $this->command->info('✅ Seeders completados');
    }
}
