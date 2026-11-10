<?php

namespace App\Http\Controllers;

use App\Http\Requests\VentaRequest;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    public function __construct(private VentaService $ventaService) {}

    // RF-10: Pantalla de nueva venta con catálogo de productos activos
    public function create()
    {
        $productos = Producto::where('activo', true)
            ->where('cantidad_stock', '>', 0)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('productos'));
    }

    // RF-11, RF-12, RF-13, RF-16: Procesar la venta completa
    public function store(VentaRequest $request)
    {
        try {
            $venta = $this->ventaService->procesarVenta(
                $request->input('items'),
                Auth::id()
            );

            return redirect()
                ->route('ventas.show', $venta->id)
                ->with('success', "Venta registrada. Factura {$venta->factura->numero_factura} generada.");

        } catch (QueryException $e) {
            report($e);

            return back()->withInput()->with('error', 'No se pudo registrar la venta. No se aplicaron cambios.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
