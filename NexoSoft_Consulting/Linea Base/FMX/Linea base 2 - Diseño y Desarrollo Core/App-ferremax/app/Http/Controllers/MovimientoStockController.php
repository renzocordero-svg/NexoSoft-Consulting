<?php

namespace App\Http\Controllers;

use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Http\Request;

class MovimientoStockController extends Controller
{
    // Historial de movimientos de stock con filtros por tipo, producto y rango de fechas.
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'tipo' => ['nullable', 'in:entrada,salida'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('fecha_desde') ? 'after_or_equal:fecha_desde' : null]),
        ]);

        $movimientos = MovimientoStock::with(['producto', 'usuario'])
            ->when($filtros['tipo'] ?? null, fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->when($filtros['producto_id'] ?? null, fn ($q, $id) => $q->where('producto_id', $id))
            ->when($filtros['fecha_desde'] ?? null, fn ($q, $fecha) => $q->whereDate('fecha_hora', '>=', $fecha))
            ->when($filtros['fecha_hasta'] ?? null, fn ($q, $fecha) => $q->whereDate('fecha_hora', '<=', $fecha))
            ->latest('fecha_hora');
        $movimientos = $request->boolean('imprimir') ? $movimientos->get() : $movimientos->paginate(20)->withQueryString();

        $productos = Producto::orderBy('nombre')->get(['id', 'nombre']);

        return view('movimientos.index', compact('movimientos', 'productos', 'filtros'));
    }
}
