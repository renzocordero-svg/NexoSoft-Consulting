@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Bienvenido, {{ $user->nombre_usuario }}</h3>
        <p class="text-muted mb-0">
            Rol:
            <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-primary' }} text-uppercase">
                {{ $user->rol }}
            </span>
        </p>
    </div>
    <small class="text-muted">{{ now()->format('d/m/Y') }}</small>
</div>

{{-- ── Tarjetas de acceso rápido ─────────────────────────────────────────── --}}
<div class="row g-4">

    {{-- Ventas: visible para todos --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                        <i class="bi bi-cart3 text-primary" style="font-size:1.8rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">Nueva Venta</h5>
                        <small class="text-muted">Registrar una venta</small>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">
                    Selecciona productos, define cantidades y genera la factura automáticamente.
                </p>
                <a href="{{ \Illuminate\Support\Facades\Route::has('ventas.create') ? route('ventas.create') : '#' }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i>Iniciar venta
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10">
                        <i class="bi bi-receipt text-success" style="font-size:1.8rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">Historial de Ventas</h5>
                        <small class="text-muted">Consultar facturas</small>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">
                    Revisa las ventas realizadas y descarga las facturas emitidas.
                </p>
                <a href="{{ \Illuminate\Support\Facades\Route::has('ventas.index') ? route('ventas.index') : '#' }}" class="btn btn-success mt-2">
                    <i class="bi bi-list-ul me-1"></i>Ver historial
                </a>
            </div>
        </div>
    </div>

    {{-- Bloque exclusivo para administrador --}}
    @if($user->isAdmin())

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10">
                        <i class="bi bi-box-seam text-warning" style="font-size:1.8rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">Productos</h5>
                        <small class="text-muted">Gestión de inventario</small>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">
                    Alta, baja, modificación de productos y control de stock mínimo.
                </p>
                <a href="{{ \Illuminate\Support\Facades\Route::has('productos.index') ? route('productos.index') : '#' }}" class="btn btn-warning mt-2">
                    <i class="bi bi-pencil-square me-1"></i>Gestionar
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10">
                        <i class="bi bi-people text-danger" style="font-size:1.8rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">Usuarios</h5>
                        <small class="text-muted">Gestión de accesos</small>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">
                    Crea y administra cuentas de vendedores y otros administradores.
                </p>
                <a href="{{ \Illuminate\Support\Facades\Route::has('usuarios.index') ? route('usuarios.index') : '#' }}" class="btn btn-danger mt-2">
                    <i class="bi bi-person-plus me-1"></i>Administrar
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-secondary bg-opacity-10">
                        <i class="bi bi-arrow-left-right text-secondary" style="font-size:1.8rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-semibold">Movimientos de Stock</h5>
                        <small class="text-muted">Auditoría de inventario</small>
                    </div>
                </div>
                <p class="text-muted small flex-grow-1">
                    Registro completo de entradas, salidas y ajustes de stock.
                </p>
                <a href="{{ \Illuminate\Support\Facades\Route::has('movimientos.index') ? route('movimientos.index') : '#' }}" class="btn btn-secondary mt-2">
                    <i class="bi bi-clock-history me-1"></i>Ver movimientos
                </a>
            </div>
        </div>
    </div>

    @endif {{-- fin @if isAdmin --}}

</div>

{{-- ── Aviso de stock bajo (solo admin) ────────────────────────────────── --}}
@if($user->isAdmin())
<div class="mt-5">
    <h5 class="fw-semibold mb-3">
        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
        Productos bajo stock mínimo
    </h5>
    @php
        $bajoStock = \App\Models\Producto::where('activo', true)
            ->whereColumn('cantidad_stock', '<=', 'stock_minimo')
            ->get();
    @endphp
    @if($bajoStock->isEmpty())
        <p class="text-muted">Todos los productos tienen stock suficiente.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover bg-white">
                <thead class="table-warning">
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Stock actual</th>
                        <th class="text-center">Stock mínimo</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bajoStock as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->categoria }}</td>
                        <td class="text-center fw-bold text-danger">{{ $p->cantidad_stock }}</td>
                        <td class="text-center">{{ $p->stock_minimo }}</td>
                        <td>{{ $p->ubicacion ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif

@endsection
