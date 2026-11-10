<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    // RF-08: Listado con buscador
    public function index(Request $request)
    {
        $buscar = $request->input('buscar', '');

        $productos = Producto::when($buscar, function ($query) use ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('categoria', 'like', "%{$buscar}%")
                    ->orWhere('proveedor', 'like', "%{$buscar}%");
            });
        })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('productos.index', compact('productos', 'buscar'));
    }

    public function create()
    {
        return view('productos.create');
    }

    // RF-05: Crear producto + MovimientoStock de entrada con la cantidad inicial
    public function store(ProductoRequest $request)
    {
        DB::transaction(function () use ($request) {
            $datos = $request->validated();
            $datos['activo'] = $request->boolean('activo');

            $producto = Producto::create($datos);

            if ($producto->cantidad_stock > 0) {
                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'entrada',
                    'cantidad' => $producto->cantidad_stock,
                    'usuario_id' => auth()->id(),
                    'motivo' => 'compra',
                    'fecha_hora' => now(),
                ]);
            }
        });

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    // RF-06: Editar producto
    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    // RF-06: Actualizar; si cambia el stock, registra MovimientoStock de ajuste
    public function update(ProductoRequest $request, Producto $producto)
    {
        DB::transaction(function () use ($request, $producto) {
            $datos = $request->validated();
            $datos['activo'] = $request->boolean('activo');

            $producto = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
            unset($datos['cantidad_stock']);
            $anteriores = $producto->only(['precio_unitario', 'precio_costo']);
            $producto->update($datos);
            if ($anteriores !== $producto->only(['precio_unitario', 'precio_costo'])) {
                Log::info('Cambio de precio', ['producto_id' => $producto->id, 'usuario_id' => auth()->id(), 'antes' => $anteriores, 'despues' => $producto->only(['precio_unitario', 'precio_costo'])]);
            }
        });

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // RF-07: Eliminar o desactivar según tenga movimientos
    public function destroy(Producto $producto)
    {
        $tieneMovimientos = $producto->movimientosStock()->exists()
                         || $producto->detalleVentas()->exists();

        if ($tieneMovimientos) {
            $producto->update(['activo' => false]);

            return redirect()->route('productos.index')
                ->with('success', 'El producto tiene movimientos registrados y fue desactivado.');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function imprimir(Request $request)
    {
        $buscar = $request->string('buscar')->toString();
        $productos = Producto::where('activo', true)->when($buscar, fn ($q) => $q->where(function ($q) use ($buscar) {
            $q->where('nombre', 'like', "%{$buscar}%")->orWhere('categoria', 'like', "%{$buscar}%")->orWhere('proveedor', 'like', "%{$buscar}%");
        }))->orderBy('nombre')->get();

        return view('productos.imprimir', compact('productos'));
    }

    public function stock(Request $request, Producto $producto, InventarioService $service)
    {
        $data = $request->validate(['tipo' => ['required', 'in:entrada,salida'], 'cantidad' => ['required', 'integer', 'min:1', 'max:999999'], 'motivo' => ['required', 'in:compra,ajuste']]);
        try {
            $service->mover($producto->id, $data['tipo'], (int) $data['cantidad'], $data['motivo'], auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Movimiento registrado correctamente.');
    }
}
