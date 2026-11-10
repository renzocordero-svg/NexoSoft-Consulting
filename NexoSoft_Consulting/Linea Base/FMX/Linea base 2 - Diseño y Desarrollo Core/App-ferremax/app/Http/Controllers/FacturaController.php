<?php

namespace App\Http\Controllers;

use App\Models\Venta;

class FacturaController extends Controller
{
    public function __invoke(Venta $venta)
    {
        abort_unless(auth()->user()->isAdmin() || $venta->usuario_id === auth()->id(), 403);
        $venta->load(['detalles.producto', 'factura', 'usuario']);
        abort_unless($venta->factura, 404);

        return view('ventas.show', compact('venta'));
    }
}
