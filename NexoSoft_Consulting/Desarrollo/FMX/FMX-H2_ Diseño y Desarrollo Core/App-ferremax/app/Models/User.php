<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre_usuario',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'usuario_id');
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isVendedor(): bool
    {
        return $this->rol === 'vendedor';
    }
}
