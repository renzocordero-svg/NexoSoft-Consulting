<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FiltrosVentas
{
    public static function validar(Request $request): array
    {
        return $request->validate([
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('fecha_desde') ? 'after_or_equal:fecha_desde' : null]),
            'usuario_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);
    }

    public static function aplicar($query, array $filtros, string $prefix = '')
    {
        return $query->when($filtros['fecha_desde'] ?? null, fn ($q, $v) => $q->whereDate($prefix.'fecha_hora', '>=', $v))
            ->when($filtros['fecha_hasta'] ?? null, fn ($q, $v) => $q->whereDate($prefix.'fecha_hora', '<=', $v))
            ->when($filtros['usuario_id'] ?? null, fn ($q, $v) => $q->where($prefix.'usuario_id', $v));
    }
}
