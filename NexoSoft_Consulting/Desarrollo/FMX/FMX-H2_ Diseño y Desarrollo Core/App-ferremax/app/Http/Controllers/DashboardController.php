<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // SC-16: la consulta estaba dentro de la vista; ahora la arma el controlador
        // y solo se ejecuta para el administrador, que es el único que ve el aviso.
        $bajoStock = $user->isAdmin()
            ? Producto::where('activo', true)
                ->whereColumn('cantidad_stock', '<=', 'stock_minimo')
                ->orderBy('nombre')
                ->get()
            : collect();

        return view('dashboard.index', compact('user', 'bajoStock'));
    }
}
