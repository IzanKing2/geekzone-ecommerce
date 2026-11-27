<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        // 'role' NO es asignable en masa: evita escalar privilegios con un
        // User::create($request->all()) futuro. Se asigna explícitamente ($user->role = …).
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS JWT (requeridos por JWTSubject)
    // ——————————————————————————————————————————————————————————————————————————

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS AUXILIARES
    // ——————————————————————————————————————————————————————————————————————————

    public function esAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ——————————————————————————————————————————————————————————————————————————
    // RELACIONES
    // ——————————————————————————————————————————————————————————————————————————

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<Cart, $this> */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
