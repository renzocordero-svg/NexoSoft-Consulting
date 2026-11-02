<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimiento_stocks';

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'fecha_hora',
        'usuario_id',
        'motivo',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
