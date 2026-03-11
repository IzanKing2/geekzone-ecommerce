<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ——————————————————————————————————————————————————————————————————————
        // ADMINISTRADOR
        // ——————————————————————————————————————————————————————————————————————
        User::create([
            'name' => 'Admin GeekZone',
            'email' => 'admin@geekzone.com',
            'password' => 'admin123',  // Se hashea automáticamente
            'role' => 'admin',
        ]);

        // ——————————————————————————————————————————————————————————————————————
        // CLIENTE
        // ——————————————————————————————————————————————————————————————————————
        User::create([
            'name' => 'Cliente Prueba',
            'email' => 'user@geekzone.com',
            'password' => 'user123',  // Se hashea automáticamente
            'role' => 'user',
        ]);
    }
}
