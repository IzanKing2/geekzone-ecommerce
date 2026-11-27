<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use CreateBaseData;
    use RefreshDatabase;

    private const GOOGLE_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private function credentials(array $extra = []): array
    {
        return array_merge([
            'email' => 'test@geekzone.com',
            'password' => 'password123',
        ], $extra);
    }

    private function registerData(array $extra = []): array
    {
        return array_merge([
            'name' => 'Nuevo',
            'surname' => 'Usuario',
            'username' => 'nuevo',
            'email' => 'nuevo@geekzone.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $extra);
    }

    // ——— reCAPTCHA ———

    public function test_login_without_captcha_token_is_rejected_when_enabled(): void
    {
        config(['services.recaptcha.enabled' => true]);
        Http::fake();
        $this->createNormalUser();

        $this->postJson('/api/login', $this->credentials())
            ->assertStatus(422)
            ->assertJsonValidationErrors('recaptcha_token');

        Http::assertNothingSent();
    }

    public function test_login_with_invalid_captcha_is_rejected(): void
    {
        config(['services.recaptcha.enabled' => true]);
        Http::fake([self::GOOGLE_URL => Http::response(['success' => false])]);
        $this->createNormalUser();

        $this->postJson('/api/login', $this->credentials(['recaptcha_token' => 'malo']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('recaptcha_token');
    }

    public function test_login_with_valid_captcha_succeeds(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret_key' => 'secreto']);
        Http::fake([self::GOOGLE_URL => Http::response(['success' => true])]);
        $this->createNormalUser();

        $this->postJson('/api/login', $this->credentials(['recaptcha_token' => 'bueno']))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['token']]);

        Http::assertSent(fn ($request) => $request['secret'] === 'secreto' && $request['response'] === 'bueno');
    }

    public function test_register_without_captcha_token_is_rejected_when_enabled(): void
    {
        config(['services.recaptcha.enabled' => true]);
        Http::fake();

        $this->postJson('/api/register', $this->registerData())
            ->assertStatus(422)
            ->assertJsonValidationErrors('recaptcha_token');

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@geekzone.com']);
    }

    public function test_register_with_valid_captcha_succeeds(): void
    {
        config(['services.recaptcha.enabled' => true]);
        Http::fake([self::GOOGLE_URL => Http::response(['success' => true])]);

        $this->postJson('/api/register', $this->registerData(['recaptcha_token' => 'bueno']))
            ->assertStatus(201);
    }

    public function test_captcha_fails_closed_when_google_is_unreachable(): void
    {
        config(['services.recaptcha.enabled' => true]);
        Http::fake([self::GOOGLE_URL => fn () => throw new ConnectionException('timeout')]);
        $this->createNormalUser();

        $this->postJson('/api/login', $this->credentials(['recaptcha_token' => 'bueno']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('recaptcha_token');
    }

    // ——— Logout / JWT ———

    public function test_token_is_invalid_after_logout(): void
    {
        $headers = $this->loginAsUser();

        $this->withHeaders($headers)->getJson('/api/perfil')->assertStatus(200);

        $this->withHeaders($headers)->postJson('/api/logout')->assertStatus(200);

        $this->withHeaders($headers)->getJson('/api/perfil')
            ->assertStatus(401)
            ->assertJson(['message' => 'Token inválido']);
    }

    public function test_request_without_token_returns_401(): void
    {
        $this->getJson('/api/perfil')->assertStatus(401);
    }

    public function test_token_of_deleted_user_returns_401(): void
    {
        $headers = $this->loginAsUser();
        User::where('email', 'test@geekzone.com')->forceDelete();

        $this->withHeaders($headers)->getJson('/api/perfil')->assertStatus(401);
    }
}
