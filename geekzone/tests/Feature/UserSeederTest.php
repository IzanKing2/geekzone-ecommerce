<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    private function sembrarEn(string $entorno): void
    {
        $this->app['env'] = $entorno;
        // Directo, no con $this->seed(): el comando db:seed pide confirmación en production
        $this->app->make(UserSeeder::class)->run();
    }

    public function test_local_creates_known_test_users(): void
    {
        $this->sembrarEn('local');

        $this->assertSame('admin', User::where('email', 'admin@geekzone.com')->value('role'));
        $this->assertSame('user', User::where('email', 'user@geekzone.com')->value('role'));
        $this->assertTrue(Hash::check('admin123', User::where('email', 'admin@geekzone.com')->value('password')));
    }

    public function test_local_seeding_is_idempotent(): void
    {
        $this->sembrarEn('local');
        $this->sembrarEn('local');

        $this->assertSame(2, User::count());
    }

    public function test_production_without_admin_variables_creates_no_users(): void
    {
        config(['app.admin_email' => null, 'app.admin_password' => null]);

        $this->sembrarEn('production');

        $this->assertSame(0, User::count());
        $this->assertDatabaseMissing('users', ['email' => 'admin@geekzone.com']);
    }

    public function test_production_creates_only_the_configured_admin(): void
    {
        config(['app.admin_email' => 'jefe@tienda.com', 'app.admin_password' => 'clave-segura-123']);

        $this->sembrarEn('production');

        $this->assertSame(1, User::count());
        $admin = User::where('email', 'jefe@tienda.com')->first();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('clave-segura-123', $admin->password));
        $this->assertDatabaseMissing('users', ['email' => 'user@geekzone.com']);
    }

    public function test_production_does_not_overwrite_existing_admin_password(): void
    {
        config(['app.admin_email' => 'jefe@tienda.com', 'app.admin_password' => 'primera-clave-123']);
        $this->sembrarEn('production');

        config(['app.admin_password' => 'otra-clave-456']);
        $this->sembrarEn('production');

        $this->assertTrue(Hash::check('primera-clave-123', User::where('email', 'jefe@tienda.com')->value('password')));
    }
}
