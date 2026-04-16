<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, CreateBaseData;

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE REGISTER
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Registro exitoso ——————————————————————————————————————————————————————————————————
    public function test_register_successfully(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Nuevo Usuario',
            'surname'               => 'Apellido Nuevo',
            'username'              => 'nuevo_usuario',
            'email'                 => 'nuevo@geekzone.com',
            'password'              => 'test123',
            'password_confirmation' => 'test123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'token',
                'type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@geekzone.com',
            'role' => 'user',
        ]);
    }

    // ———— Registro con email existente ——————————————————————————————————————————————————————————————————
    public function test_register_with_existing_email_error(): void
    {
        $this->createNormalUser();

        $response = $this->postJson('/api/register', [
            'name' => 'Otro Usuario',
            'email' => 'test@geekzone.com',
            'password' => 'test123',
            'password_confirmation' => 'test123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    // ———— Registro con datos inválidos ——————————————————————————————————————————————————————————————————
    public function test_register_with_invalid_data_error(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE LOGIN
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Inicio de sesión exitoso ——————————————————————————————————————————————————————————————————
    public function test_login_successfully(): void
    {
        $this->createNormalUser();

        $response = $this->postJson('/api/login', [
            'email' => 'test@geekzone.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'token',
                'type',
            ]);
    }

    // ———— Inicio de sesión con contraseña incorrecta ——————————————————————————————————————————————————————————————————
    public function test_login_with_invalid_password_error(): void
    {
        $this->createNormalUser();

        $response = $this->postJson('/api/login', [
            'email' => 'test@geekzone.com',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciales incorrectas. Verifica tu email y contraseña.',
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE LOGOUT
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Cierre de sesión exitoso ——————————————————————————————————————————————————————————————————
    public function test_logout_successfully(): void
    {
        $headers = $this->loginAsUser();

        $response = $this->postJson('/api/logout', [], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sesión cerrada correctamente.',
            ]);
    }

    // ———— Cierre de sesión con token inválido ——————————————————————————————————————————————————————————————————
    public function test_logout_with_invalid_token_error(): void
    {
        $headers = $this->getHeadersWithToken('invalid-token');

        $response = $this->postJson('/api/logout', [], $headers);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Token no encontrado',
            ]);
    }
}
