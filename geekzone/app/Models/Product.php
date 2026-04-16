<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'featured',
        'image_url',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'featured' => 'boolean'
    ];

    // ——————————————————————————————————————————————————————————————————————————
    // ACCESSORS
    // ——————————————————————————————————————————————————————————————————————————

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value && !str_starts_with($value, 'http')
                ? asset($value)
                : $value
        );
    }

    // ——————————————————————————————————————————————————————————————————————————
    // RELACIONES
    // ——————————————————————————————————————————————————————————————————————————

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
