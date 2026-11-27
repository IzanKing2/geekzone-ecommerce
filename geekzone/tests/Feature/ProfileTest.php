<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    private array $headers;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headers = $this->loginAsUser(); // test@geekzone.com / password123
        $this->user = User::where('email', 'test@geekzone.com')->first();
    }

    private function actualizar(array $datos)
    {
        return $this->withHeaders($this->headers)->putJson('/api/perfil', $datos);
    }

    private function crearOtroUsuario(): User
    {
        return User::create([
            'name' => 'Otro',
            'surname' => 'Usuario',
            'username' => 'otro_usuario',
            'email' => 'otro@geekzone.com',
            'password' => 'password123',
        ]);
    }

    // ——— Lectura ———

    public function test_show_profile(): void
    {
        $this->withHeaders($this->headers)->getJson('/api/perfil')
            ->assertStatus(200)
            ->assertJsonPath('data.user.email', 'test@geekzone.com')
            ->assertJsonMissingPath('data.user.password');
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/perfil')->assertStatus(401);
        $this->putJson('/api/perfil', ['name' => 'X'])->assertStatus(401);
    }

    // ——— Datos básicos ———

    public function test_update_basic_data(): void
    {
        $this->actualizar(['name' => 'Nuevo Nombre', 'surname' => 'Nuevo Apellido'])
            ->assertStatus(200)
            ->assertJsonPath('data.user.name', 'Nuevo Nombre');

        $this->assertSame('Nuevo Apellido', $this->user->fresh()->surname);
    }

    public function test_update_does_not_return_a_new_token_without_password_change(): void
    {
        $this->actualizar(['name' => 'Otro Nombre'])->assertJsonMissingPath('data.token');
    }

    public function test_cannot_take_an_existing_username_or_email(): void
    {
        $this->crearOtroUsuario();

        $this->actualizar(['username' => 'otro_usuario'])->assertStatus(422)->assertJsonValidationErrors('username');
        $this->actualizar(['email' => 'otro@geekzone.com'])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_can_keep_own_username_and_email(): void
    {
        $this->actualizar(['username' => 'usuario_test', 'email' => 'test@geekzone.com'])->assertStatus(200);
    }

    public function test_role_cannot_be_changed_through_the_profile(): void
    {
        $this->actualizar(['role' => 'admin'])->assertStatus(200);

        $this->assertSame('user', $this->user->fresh()->role);
    }

    // ——— Contraseña ———

    public function test_password_change_requires_current_password(): void
    {
        $this->actualizar(['password' => 'nueva-clave-123', 'password_confirmation' => 'nueva-clave-123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('password123', $this->user->fresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $this->actualizar([
            'current_password' => 'no-es-esta',
            'password' => 'nueva-clave-123',
            'password_confirmation' => 'nueva-clave-123',
        ])->assertStatus(422)->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('password123', $this->user->fresh()->password));
    }

    public function test_password_change_enforces_minimum_length_and_difference(): void
    {
        $this->actualizar([
            'current_password' => 'password123',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertStatus(422)->assertJsonValidationErrors('password');

        $this->actualizar([
            'current_password' => 'password123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_password_change_requires_confirmation(): void
    {
        $this->actualizar([
            'current_password' => 'password123',
            'password' => 'nueva-clave-123',
            'password_confirmation' => 'otra-distinta-123',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_password_change_invalidates_old_token_and_returns_a_new_one(): void
    {
        $response = $this->actualizar([
            'current_password' => 'password123',
            'password' => 'nueva-clave-123',
            'password_confirmation' => 'nueva-clave-123',
        ])->assertStatus(200)->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertTrue(Hash::check('nueva-clave-123', $this->user->fresh()->password));

        // El token anterior ya no sirve…
        $this->withHeaders($this->headers)->getJson('/api/perfil')->assertStatus(401);

        // …y el nuevo sí
        $this->withHeaders($this->getHeadersWithToken($response->json('data.token')))
            ->getJson('/api/perfil')->assertStatus(200);
    }

    public function test_can_login_with_the_new_password_only(): void
    {
        $this->actualizar([
            'current_password' => 'password123',
            'password' => 'nueva-clave-123',
            'password_confirmation' => 'nueva-clave-123',
        ])->assertStatus(200);

        $this->postJson('/api/login', ['email' => 'test@geekzone.com', 'password' => 'nueva-clave-123'])->assertStatus(200);
        $this->postJson('/api/login', ['email' => 'test@geekzone.com', 'password' => 'password123'])->assertStatus(401);
    }

    // ——— Registro ———

    public function test_register_rejects_duplicate_username(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Nuevo', 'surname' => 'Usuario', 'username' => 'usuario_test',
            'email' => 'nuevo@geekzone.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('username');
    }

    public function test_register_requires_8_characters_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Nuevo', 'surname' => 'Usuario', 'username' => 'nuevo',
            'email' => 'nuevo@geekzone.com', 'password' => '1234567', 'password_confirmation' => '1234567',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_register_cannot_create_an_admin(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Intruso', 'surname' => 'Usuario', 'username' => 'intruso',
            'email' => 'intruso@geekzone.com', 'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'admin', 'rol' => 'admin',
        ])->assertStatus(201);

        $this->assertSame('user', User::where('email', 'intruso@geekzone.com')->value('role'));
    }
}
