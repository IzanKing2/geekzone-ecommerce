<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ——————————————————————————————————————————————————————————————————————
        // LOCAL / TESTING: usuarios de prueba conocidos (admin123 / user123)
        // ——————————————————————————————————————————————————————————————————————
        if (app()->environment(['local', 'testing'])) {
            $this->crearUsuario('Admin', 'GeekZone', 'Administrador', 'admin@geekzone.com', 'admin123', 'admin');
            $this->crearUsuario('User', 'GeekZone', 'Usuario', 'user@geekzone.com', 'user123', 'user');

            return;
        }

        // ——————————————————————————————————————————————————————————————————————
        // OTROS ENTORNOS: nunca credenciales conocidas. Solo un administrador
        // si se definen ADMIN_EMAIL y ADMIN_PASSWORD.
        // ——————————————————————————————————————————————————————————————————————
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (! $email || ! $password) {
            $this->command?->warn('UserSeeder: entorno "'.app()->environment().'" sin ADMIN_EMAIL/ADMIN_PASSWORD; no se crea ningún usuario.');

            return;
        }

        // No se pisa la contraseña de un administrador que ya exista
        if (User::where('email', $email)->exists()) {
            return;
        }

        $this->crearUsuario('Admin', 'GeekZone', 'Administrador', $email, $password, 'admin');
    }

    /** Crea el usuario o actualiza el existente (idempotente: se puede sembrar varias veces). */
    private function crearUsuario(string $name, string $surname, string $username, string $email, string $password, string $role): void
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->fill(compact('name', 'surname', 'username', 'password'));
        $user->role = $role;
        $user->save();
    }
}
