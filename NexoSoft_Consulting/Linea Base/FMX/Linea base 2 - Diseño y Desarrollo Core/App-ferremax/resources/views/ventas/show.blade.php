@extends('layouts.app')

@section('title', 'Factura ' . $venta->factura->numero_factura)

@push('styles')
<style>
/* ── Estilos normales ── */
#facturaImprimible {
    max-width: 780px;
    margin: 0 auto;
    background: white;
    border-radius: 8px;
    padding: 40px;
    box-shadow: 0 2px 16px rgba(0,0,0,.08);
}
.factura-header { border-bottom: 3px solid #212529; padding-bottom: 20px; margin-bottom: 24px; }
.factura-numero { font-size: 1.4rem; font-weight: 700; color: #212529; }
.tabla-factura th { background: #212529; color: #fff; font-size: .85rem; }
.tabla-factura td { font-size: .9rem; }
.total-box { background: #212529; color: #fff; border-radius: 6px; padding: 16px 24px; }

/* ── Estilos de impresión ── */
@media print {
    /* Ocultar todo lo que no es la factura */
    nav.navbar,
    nav.sidebar,
    .d-print-none,
    .alert,
    .btn-volver {
        display: none !important;
    }
    /* El main ocupa todo el ancho */
    .col-md-10 {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        padding: 0 !important;
    }
    body { background: white !important; }
    #facturaImprimible {
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 20px !important;
    }
}
</style>
@endpush

@section('content')

{{-- Barra de acciones (desaparece al imprimir) --}}
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-secondary btn-volver">
            <i class="bi bi-arrow-left"></i> Historial
        </a>
        <h5 class="fw-bold mb-0 ms-2">
            Factura <span class="text-primary">{{ $venta->factura->numero_factura }}</span>
        </h5>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ventas.create') }}" class="btn btn-outline-success">
            <i class="bi bi-plus-circle me-1"></i>Nueva venta
        </a>
        <button onclick="window.print()" class="btn btn-primary fw-semibold">
            <i class="bi bi-printer me-1"></i>Imprimir factura
        </button>
    </div>
</div>

{{-- ── FACTURA IMPRIMIBLE ─────────────────────────────────────────────── --}}
<div id="facturaImprimible">

    {{-- Encabezado --}}
    <div class="factura-header row align-items-start">
        <div class="col-7">
            <div style="font-size:2rem; font-weight:900; letter-spacing:1px;">
                FERRE<span style="color:#ffc107;">MAX</span>
            </div>
            <div class="text-muted" style="font-size:.85rem; margin-top:4px;">
                Sistema de Gestión de Almacén<br>
                Ferretería y Materiales de Construcción
            </div>
        </div>
        <div class="col-5 text-end">
            <div class="factura-numero">FACTURA</div>
            <div style="font-size:1.2rem; color:#6c757d; font-weight:600;">
                {{ $venta->factura->numero_factura }}
            </div>
            <div class="text-muted small mt-1">
                Emisión: {{ $venta->factura->fecha_emision->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    {{-- Datos de la venta --}}
    <div class="row mb-4">
        <div class="col-6">
            <p class="text-muted small mb-1 fw-semibold text-uppercase">Vendedor</p>
            <p class="mb-0 fw-semibold">{{ $venta->usuario->nombre_usuario }}</p>
            <p class="text-muted small mb-0">{{ ucfirst($venta->usuario->rol) }}</p>
        </div>
        <div class="col-6 text-end">
            <p class="text-muted small mb-1 fw-semibold text-uppercase">Fecha de venta</p>
            <p class="mb-0 fw-semibold">{{ $venta->fecha_hora->format('d/m/Y') }}</p>
            <p class="text-muted small mb-0">{{ $venta->fecha_hora->format('H:i:s') }}</p>
        </div>
    </div>

    {{-- Tabla de productos --}}
    <table class="table tabla-factura mb-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th class="text-center">Cant.</th>
                <th class="text-end">P. Unitario</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $i => $detalle)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td>
                    <div class="fw-semibold">{{ $detalle->producto->nombre }}</div>
                    <small class="text-muted">{{ $detalle->producto->categoria }}</small>
                </td>
                <td class="text-center fw-semibold">{{ $detalle->cantidad }}</td>
                <td class="text-end">${{ number_format($detalle->precio_unitario_momento, 2) }}</td>
                <td class="text-end fw-semibold">${{ number_format($detalle->subtotal_linea, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales --}}
    <div class="row justify-content-end">
        <div class="col-md-5">
            <table class="table table-sm mb-3">
                <tr>
                    <td class="text-muted">Subtotal</td>
                    <td class="text-end fw-semibold">${{ number_format($venta->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">IGV (18%)</td>
                    <td class="text-end fw-semibold">${{ number_format($venta->impuestos, 2) }}</td>
                </tr>
            </table>
            <div class="total-box d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-5">TOTAL</span>
                <span class="fw-bold fs-4">${{ number_format($venta->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Pie de página --}}
    <div class="text-center text-muted mt-5" style="font-size:.78rem; border-top:1px solid #dee2e6; padding-top:16px;">
        Documento generado por FERREMAX · {{ $venta->factura->numero_factura }} · {{ now()->format('d/m/Y H:i') }}<br>
        Gracias por su compra.
    </div>

</div>

@endsection
