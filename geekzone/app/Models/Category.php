<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'image_url',
    ];

    // ——————————————————————————————————————————————————————————————————————————
    // ACCESSORS
    // ——————————————————————————————————————————————————————————————————————————

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value && ! str_starts_with($value, 'http')
                ? asset($value)
                : $value
        );
    }

    // ——————————————————————————————————————————————————————————————————————————
    // RELACIONES
    // ——————————————————————————————————————————————————————————————————————————

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
