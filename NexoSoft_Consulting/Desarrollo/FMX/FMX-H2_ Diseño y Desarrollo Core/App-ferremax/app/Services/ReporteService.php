<?php

namespace App\Services;

use App\Models\Venta;
use App\Support\FiltrosVentas;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function generar(array $filtros): array
    {
        $ventas = FiltrosVentas::aplicar(Venta::query(), $filtros)->orderBy('fecha_hora')->get(['id', 'fecha_hora', 'total']);
        $ingresos = (float) $ventas->sum('total');
        $base = FiltrosVentas::aplicar(DB::table('detalle_ventas')->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')->join('productos', 'productos.id', '=', 'detalle_ventas.producto_id'), $filtros, 'ventas.');
        $ganancia = (float) (clone $base)->selectRaw('COALESCE(SUM(detalle_ventas.subtotal_linea - detalle_ventas.costo_unitario_momento * detalle_ventas.cantidad),0) AS valor')->value('valor');
        $agregados = (clone $base)->groupBy('productos.id', 'productos.nombre')->selectRaw('productos.id, productos.nombre, SUM(detalle_ventas.cantidad) AS cantidad_vendida, SUM(detalle_ventas.subtotal_linea) AS ingreso_generado')->get();
        $masVendidos = $agregados->sortBy([['cantidad_vendida', 'desc'], ['nombre', 'asc']])->take(5)->values();
        $menosVendidos = $agregados->sortBy([['cantidad_vendida', 'asc'], ['nombre', 'asc']])->take(5)->values();
        $diarias = $ventas->groupBy(fn ($v) => $v->fecha_hora->format('Y-m-d'))->map(fn ($g) => round((float) $g->sum('total'), 2));
        $labels = [];
        $data = [];
        foreach (CarbonPeriod::create($filtros['fecha_desde'], $filtros['fecha_hasta']) as $fecha) {
            $key = $fecha->format('Y-m-d');
            $labels[] = $key;
            $data[] = $diarias[$key] ?? 0;
        }

        return ['resumen' => ['ingresos' => round($ingresos, 2), 'ganancia' => round($ganancia, 2), 'numero_ventas' => $ventas->count(), 'ticket_promedio' => $ventas->count() ? round($ingresos / $ventas->count(), 2) : 0], 'ventasPorDia' => compact('labels', 'data'), 'masVendidos' => $masVendidos, 'menosVendidos' => $menosVendidos];
    }
}
