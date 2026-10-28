<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use App\Support\FiltrosVentas;
use Illuminate\Http\Request;

class HistorialVentaController extends Controller
{
    public function __invoke(Request $request)
    {
        $filtros = FiltrosVentas::validar($request);
        if (! auth()->user()->isAdmin()) {
            $filtros['usuario_id'] = auth()->id();
        }
        $ventas = FiltrosVentas::aplicar(Venta::with(['usuario', 'factura'])->withCount('detalles'), $filtros)->latest('fecha_hora')->latest('id')->paginate(15)->withQueryString();
        $usuarios = auth()->user()->isAdmin() ? User::orderBy('nombre_usuario')->get() : collect();

        return view('ventas.index', compact('ventas', 'usuarios', 'filtros'));
    }
}
