@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')
@include('partials.filtros-ventas')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-success"></i>Historial de Ventas</h3>
        <p class="text-muted mb-0 small">
            @if(auth()->user()->isAdmin())
                Todas las ventas registradas en el sistema.
            @else
                Tus ventas registradas.
            @endif
        </p>
    </div>
    <a href="{{ route('ventas.create') }}" class="btn btn-success fw-semibold">
        <i class="bi bi-plus-circle me-1"></i>Nueva venta
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($ventas->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-receipt" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">No hay ventas registradas aún.</p>
                <a href="{{ route('ventas.create') }}" class="btn btn-success btn-sm mt-3">
                    Registrar primera venta
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nº Factura</th>
                            <th>Fecha y hora</th>
                            @if(auth()->user()->isAdmin())
                                <th>Vendedor</th>
                            @endif
                            <th class="text-center">Ítems</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">IVA</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventas as $venta)
                        <tr>
                            <td class="fw-semibold text-primary">
                                {{ $venta->factura->numero_factura ?? '—' }}
                            </td>
                            <td>
                                <div>{{ $venta->fecha_hora->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $venta->fecha_hora->format('H:i') }}</small>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td>{{ $venta->usuario->nombre_usuario ?? '—' }}</td>
                            @endif
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $venta->detalles_count }}</span>
                            </td>
                            <td class="text-end">${{ number_format($venta->subtotal, 2) }}</td>
                            <td class="text-end text-muted">${{ number_format($venta->impuestos, 2) }}</td>
                            <td class="text-end fw-bold">${{ number_format($venta->total, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('ventas.show', $venta) }}"
                                   class="btn btn-sm btn-outline-primary" title="Ver factura">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <small class="text-muted">
                    Mostrando {{ $ventas->firstItem() }}–{{ $ventas->lastItem() }}
                    de {{ $ventas->total() }} venta(s)
                </small>
                {{ $ventas->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
