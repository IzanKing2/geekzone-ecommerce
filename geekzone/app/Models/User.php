<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
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
            'rol' => $this->rol,
        ];
    }

    // ——————————————————————————————————————————————————————————————————————————
    // MÉTODOS AUXILIARES
    // ——————————————————————————————————————————————————————————————————————————

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    // ——————————————————————————————————————————————————————————————————————————
    // RELACIONES
    // ——————————————————————————————————————————————————————————————————————————

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class);
    }
}
