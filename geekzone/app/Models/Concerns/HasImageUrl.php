<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Convierte el campo image_url en una URL absoluta al leerlo.
 *
 * Las rutas relativas (img/..., /storage/...) se pasan por asset(); las URLs
 * externas (http...) se dejan tal cual. En la base de datos se guarda siempre el valor original.
 */
trait HasImageUrl
{
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value && ! str_starts_with($value, 'http')
                ? asset($value)
                : $value
        );
    }
}
