<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FiltrosVentas
{
    public static function validar(Request $request): array
    {
        return $request->validate([
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('fecha_desde') ? 'after_or_equal:fecha_desde' : null]),
            'usuario_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            // SC-13: búsqueda por número de factura (completo o parcial, p. ej. FACT-000123 o 123)
            'numero_factura' => ['nullable', 'string', 'max:20'],
        ]);
    }

    public static function aplicar($query, array $filtros, string $prefix = '')
    {
        return $query->when($filtros['fecha_desde'] ?? null, fn ($q, $v) => $q->whereDate($prefix.'fecha_hora', '>=', $v))
            ->when($filtros['fecha_hasta'] ?? null, fn ($q, $v) => $q->whereDate($prefix.'fecha_hora', '<=', $v))
            ->when($filtros['usuario_id'] ?? null, fn ($q, $v) => $q->where($prefix.'usuario_id', $v))
            ->when(trim($filtros['numero_factura'] ?? ''), fn ($q, $v) => $q->whereExists(
                // whereExists sirve tanto para Eloquent (historial) como para el query builder de reportes.
                fn ($sub) => $sub->select(DB::raw(1))
                    ->from('facturas')
                    ->whereColumn('facturas.venta_id', 'ventas.id')
                    ->where('facturas.numero_factura', 'like', '%'.$v.'%')
            ));
    }
}
