<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReporteService;
use App\Support\FiltrosVentas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    public function index(Request $request, ReporteService $service)
    {
        $filtros = FiltrosVentas::validar($request);
        $filtros['fecha_desde'] = $filtros['fecha_desde'] ?? now()->startOfMonth()->format('Y-m-d');
        $filtros['fecha_hasta'] = $filtros['fecha_hasta'] ?? now()->endOfMonth()->format('Y-m-d');
        $desde = Carbon::parse($filtros['fecha_desde']);
        $hasta = Carbon::parse($filtros['fecha_hasta']);
        if ($hasta->lt($desde) || $desde->diffInDays($hasta) > 366) {
            throw ValidationException::withMessages(['fecha_hasta' => 'Selecciona un rango válido de hasta 367 días.']);
        }

        return view('reportes.index', array_merge($service->generar($filtros), ['filtros' => $filtros, 'usuarios' => User::orderBy('nombre_usuario')->get()]));
    }
}
