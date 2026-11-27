<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreateBaseData, RefreshDatabase;

    // ══════════════════════════════════════════════════════════════════════════
    // TEST DE REGISTER
    // ══════════════════════════════════════════════════════════════════════════

    // ———— Registro exitoso ——————————————————————————————————————————————————————————————————
    public function test_register_successfully(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nuevo Usuario',
            'surname' => 'Apellido Nuevo',
            'username' => 'nuevo_usuario',
            'email' => 'nuevo@geekzone.com',
            'password' => 'test12345',
            'password_confirmation' => 'test12345',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'errors' => null])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'type',
                ],
                'errors',
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
            'password_confirmation' => 'test12345',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors',
            ]);
    }

    // ———— Registro con datos inválidos ——————————————————————————————————————————————————————————————————
    public function test_register_with_invalid_data_error(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'errors' => ['name', 'surname', 'username', 'email', 'password'],
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
            ->assertJson(['success' => true, 'errors' => null])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'type',
                ],
                'errors',
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
                'success' => false,
                'message' => 'Credenciales incorrectas. Verifica tu email y contraseña.',
                'data' => null,
            ])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
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
                'success' => true,
                'message' => 'Sesión cerrada correctamente.',
                'data' => null,
            ]);
    }

    // ———— Cierre de sesión con token inválido ——————————————————————————————————————————————————————————————————
    public function test_logout_with_invalid_token_error(): void
    {
        $headers = $this->getHeadersWithToken('invalid-token');

        $response = $this->postJson('/api/logout', [], $headers);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Token inválido',
                'data' => null,
            ])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }

    // ———— Los errores de autenticación y de rol usan el formato estándar ——————————————————————————————————————
    public function test_auth_middleware_errors_use_the_standard_envelope(): void
    {
        $this->getJson('/api/perfil')
            ->assertStatus(401)
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);

        $headers = $this->loginAsUser();

        $this->getJson('/api/admin/dashboard/resumen', $headers)
            ->assertStatus(403)
            ->assertJson(['success' => false, 'data' => null])
            ->assertJsonStructure(['success', 'message', 'data', 'errors']);
    }
}
