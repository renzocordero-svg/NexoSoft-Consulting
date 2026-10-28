@extends('layouts.app')

@section('title', 'Movimientos de Stock')

@section('content')
<a class="btn btn-outline-secondary mb-3 d-print-none" href="{{ route('movimientos.index', array_merge(request()->query(),['imprimir'=>1])) }}">Vista imprimible / Guardar PDF</a>
@if(request()->boolean('imprimir'))<button class="btn btn-warning d-print-none mb-3" onclick="window.print()">Imprimir todos los resultados filtrados</button>@endif

{{-- ── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-warning"></i>Movimientos de Stock</h3>
        <p class="text-muted mb-0 small">Trazabilidad de entradas y salidas · Solo administradores</p>
    </div>
</div>

{{-- ── Filtros ────────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('movimientos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="entrada" {{ ($filtros['tipo'] ?? '') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="salida" {{ ($filtros['tipo'] ?? '') === 'salida' ? 'selected' : '' }}>Salida</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1 small fw-semibold text-muted">Producto</label>
                <select name="producto_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" {{ (string) ($filtros['producto_id'] ?? '') === (string) $producto->id ? 'selected' : '' }}>
                            {{ $producto->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
            <div class="col-md-1">
                @if(array_filter($filtros))
                    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary w-100" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Tabla de movimientos ───────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        @if($movimientos->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">
                    @if(array_filter($filtros))
                        No se encontraron movimientos con esos filtros.
                    @else
                        Aún no hay movimientos de stock registrados.
                    @endif
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Producto</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Cantidad</th>
                            <th>Motivo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movimientos as $mov)
                        <tr>
                            <td>
                                <div>{{ $mov->fecha_hora->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $mov->fecha_hora->format('H:i') }}</small>
                            </td>
                            <td>{{ $mov->producto->nombre ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $mov->tipo === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                    <i class="bi bi-arrow-{{ $mov->tipo === 'entrada' ? 'down' : 'up' }}-circle me-1"></i>
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td class="text-center fw-semibold">{{ $mov->cantidad }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($mov->motivo) }}</span></td>
                            <td class="small">{{ $mov->usuario->nombre_usuario ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <small class="text-muted">
                    Mostrando {{ request()->boolean('imprimir') ? 1 : $movimientos->firstItem() }}–{{ request()->boolean('imprimir') ? $movimientos->count() : $movimientos->lastItem() }}
                    de {{ request()->boolean('imprimir') ? $movimientos->count() : $movimientos->total() }} movimiento(s)
                </small>
                @unless(request()->boolean('imprimir')){{ $movimientos->links('pagination::bootstrap-5') }}@endunless
            </div>
        @endif

    </div>
</div>

@endsection
