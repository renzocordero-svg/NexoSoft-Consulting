@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<a class="btn btn-outline-secondary mb-3" href="{{ route('productos.imprimir', request()->only('buscar')) }}">Imprimir existencias</a>

{{-- ── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-warning"></i>Productos</h3>
        <p class="text-muted mb-0 small">Gestión de inventario · Solo administradores</p>
    </div>
    <a href="{{ route('productos.create') }}" class="btn btn-warning fw-semibold">
        <i class="bi bi-plus-circle me-1"></i>Nuevo producto
    </a>
</div>

{{-- ── Buscador RF-08 ─────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('productos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label mb-1 small fw-semibold text-muted">Buscar por nombre, categoría o proveedor</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        placeholder="Ej: martillo, herramientas, Stanley…"
                        value="{{ $buscar }}"
                        autocomplete="off"
                    >
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
            <div class="col-md-2">
                @if($buscar)
                    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Tabla de productos ─────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        @if($productos->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">
                    @if($buscar)
                        No se encontraron productos para <strong>"{{ $buscar }}"</strong>.
                    @else
                        Aún no hay productos registrados.
                    @endif
                </p>
                @if(!$buscar)
                    <a href="{{ route('productos.create') }}" class="btn btn-warning btn-sm mt-3">
                        Crear el primero
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Proveedor</th>
                            <th>Categoría</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Mín.</th>
                            <th>Ubicación</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                        @php
                            $bajoStock = $producto->cantidad_stock <= $producto->stock_minimo;
                            $sinStock  = $producto->cantidad_stock === 0;
                        @endphp
                        <tr class="{{ $bajoStock && $producto->activo ? 'table-warning' : '' }}">
                            <td class="text-muted small">{{ $producto->id }}</td>
                            <td class="fw-semibold">
                                {{ $producto->nombre }}
                                @if($sinStock && $producto->activo)
                                    <span class="badge bg-danger ms-1">Sin stock</span>
                                @elseif($bajoStock && $producto->activo)
                                    {{-- RF-09/RF-20: indicador visual bajo stock mínimo --}}
                                    <span class="badge bg-warning text-dark ms-1">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Bajo mínimo
                                    </span>
                                @endif
                            </td>
                            <td>{{ $producto->proveedor }}</td>
                            <td><span class="badge bg-secondary">{{ $producto->categoria }}</span></td>
                            <td class="text-end fw-semibold">${{ number_format($producto->precio_unitario, 2) }}</td>
                            {{-- Stock con color según nivel RF-09 --}}
                            <td class="text-center fw-bold {{ $sinStock ? 'text-danger' : ($bajoStock ? 'text-warning' : 'text-success') }}">
                                {{ $producto->cantidad_stock }}
                            </td>
                            <td class="text-center text-muted">{{ $producto->stock_minimo }}</td>
                            <td class="text-muted small">{{ $producto->ubicacion ?? '—' }}</td>
                            <td class="text-center">
                                @if($producto->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('productos.edit', $producto) }}"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('productos.destroy', $producto) }}"
                                          class="form-delete d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar / Desactivar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <small class="text-muted">
                    Mostrando {{ $productos->firstItem() }}–{{ $productos->lastItem() }}
                    de {{ $productos->total() }} producto(s)
                </small>
                {{ $productos->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.form-delete').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();
        if (confirm('¿Eliminar este producto? Si tiene movimientos, solo se desactivará.')) {
            form.submit();
        }
    });
});
</script>
@endpush
