<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifica en servidor el token de reCAPTCHA v2 contra la API de Google.
 *
 * Comprobar solo en el navegador no protege nada: cualquiera puede llamar
 * directamente a /api/login o /api/register. Si Google no responde, la
 * validación falla (fail-closed).
 */
class Recaptcha implements ValidationRule
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** Se ejecuta también cuando el campo falta o viene vacío. */
    public bool $implicit = true;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('services.recaptcha.enabled')) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('Por favor, completa el captcha.');

            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
        } catch (Throwable $e) {
            Log::warning('reCAPTCHA: no se pudo contactar con Google', ['error' => $e->getMessage()]);
            $fail('No se pudo verificar el captcha. Inténtalo de nuevo.');

            return;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            $fail('La verificación del captcha ha fallado. Inténtalo de nuevo.');
        }
    }
}
