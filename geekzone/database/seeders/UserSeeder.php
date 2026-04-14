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
            'name'     => 'Admin',
            'surname'  => 'GeekZone',
            'username' => 'Administrador',
            'email'    => 'admin@geekzone.com',
            'password' => 'admin123',  // Se hashea automáticamente
            'role'     => 'admin',
        ]);

        // ——————————————————————————————————————————————————————————————————————
        // CLIENTE
        // ——————————————————————————————————————————————————————————————————————
        User::create([
            'name' => 'User',
            'surname'  => 'GeekZone',
            'username' => 'Usuario',
            'email' => 'user@geekzone.com',
            'password' => 'user123',  // Se hashea automáticamente
            'role' => 'user',
        ]);
    }
}
