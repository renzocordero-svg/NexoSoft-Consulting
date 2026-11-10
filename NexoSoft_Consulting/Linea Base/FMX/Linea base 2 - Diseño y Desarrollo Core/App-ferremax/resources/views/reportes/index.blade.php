@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
@include('partials.filtros-ventas')
<button class="btn btn-outline-secondary mb-3 d-print-none" onclick="window.print()">Imprimir / Guardar PDF</button>

{{-- ── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-warning"></i>Reportes</h3>
        <p class="text-muted mb-0 small">
            Del {{ $filtros['fecha_desde'] }} al {{ $filtros['fecha_hasta'] }} · Solo administradores
        </p>
    </div>
</div>

{{-- ── Tarjetas KPI ───────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-cash-stack"></i> Ingresos del período
                </div>
                <div class="fs-4 fw-bold">${{ number_format($resumen['ingresos'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-graph-up-arrow"></i> Ganancia del período
                </div>
                <div class="fs-4 fw-bold text-success">${{ number_format($resumen['ganancia'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-receipt"></i> N.º de ventas
                </div>
                <div class="fs-4 fw-bold">{{ $resumen['numero_ventas'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-calculator"></i> Ticket promedio
                </div>
                <div class="fs-4 fw-bold">${{ number_format($resumen['ticket_promedio'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Ventas por día ─────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="text-uppercase text-muted fw-semibold mb-3">
            <i class="bi bi-calendar3 me-1"></i>Ventas por día del período
        </h6>
        @if(array_sum($ventasPorDia['data']) > 0)
            <canvas id="chartVentasPorDia" height="90"></canvas>
        @else
            <p class="text-muted text-center py-4 mb-0">Aún no hay ventas registradas en este período.</p>
        @endif
    </div>
</div>

{{-- ── Top 5 más / menos vendidos (gráficos) ──────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted fw-semibold mb-3">
                    <i class="bi bi-arrow-up-circle me-1 text-primary"></i>Top 5 más vendidos
                </h6>
                @if($masVendidos->isNotEmpty())
                    <canvas id="chartMasVendidos" height="200"></canvas>
                @else
                    <p class="text-muted text-center py-4 mb-0">Sin ventas en este período.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted fw-semibold mb-3">
                    <i class="bi bi-arrow-down-circle me-1" style="color:#fd7e14"></i>Top 5 menos vendidos
                </h6>
                @if($menosVendidos->isNotEmpty())
                    <canvas id="chartMenosVendidos" height="200"></canvas>
                @else
                    <p class="text-muted text-center py-4 mb-0">Sin ventas en este período.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Top 5 más / menos vendidos (tablas) ────────────────────────────────── --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-0">
                <h6 class="text-uppercase text-muted fw-semibold p-3 pb-0">Detalle · Más vendidos</h6>
                @if($masVendidos->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Ingreso generado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($masVendidos as $p)
                                <tr>
                                    <td>{{ $p->nombre }}</td>
                                    <td class="text-center fw-semibold">{{ $p->cantidad_vendida }}</td>
                                    <td class="text-end">${{ number_format($p->ingreso_generado, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-0">
                <h6 class="text-uppercase text-muted fw-semibold p-3 pb-0">Detalle · Menos vendidos</h6>
                @if($menosVendidos->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Ingreso generado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($menosVendidos as $p)
                                <tr>
                                    <td>{{ $p->nombre }}</td>
                                    <td class="text-center fw-semibold">{{ $p->cantidad_vendida }}</td>
                                    <td class="text-end">${{ number_format($p->ingreso_generado, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const COLOR_AZUL    = '#0d6efd';
const COLOR_NARANJA = '#fd7e14';
const COLOR_GRID    = 'rgba(0,0,0,0.06)';
const COLOR_TICK    = '#6c757d';

Chart.defaults.font.family = "system-ui, -apple-system, 'Segoe UI', sans-serif";

@if(array_sum($ventasPorDia['data']) > 0)
new Chart(document.getElementById('chartVentasPorDia'), {
    type: 'line',
    data: {
        labels: @json($ventasPorDia['labels']),
        datasets: [{
            label: 'Ventas',
            data: @json($ventasPorDia['data']),
            borderColor: COLOR_AZUL,
            backgroundColor: 'rgba(13,110,253,0.10)',
            borderWidth: 2,
            pointRadius: 2,
            pointHoverRadius: 5,
            tension: 0.25,
            fill: true,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: COLOR_GRID }, ticks: { color: COLOR_TICK, callback: v => '$' + v } },
            x: { grid: { display: false }, ticks: { color: COLOR_TICK } },
        }
    }
});
@endif

@if($masVendidos->isNotEmpty())
new Chart(document.getElementById('chartMasVendidos'), {
    type: 'bar',
    data: {
        labels: @json($masVendidos->pluck('nombre')),
        datasets: [{
            label: 'Cantidad vendida',
            data: @json($masVendidos->pluck('cantidad_vendida')),
            backgroundColor: COLOR_AZUL,
            borderRadius: 4,
            maxBarThickness: 28,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: COLOR_GRID }, ticks: { color: COLOR_TICK, precision: 0 } },
            y: { grid: { display: false }, ticks: { color: COLOR_TICK } },
        }
    }
});
@endif

@if($menosVendidos->isNotEmpty())
new Chart(document.getElementById('chartMenosVendidos'), {
    type: 'bar',
    data: {
        labels: @json($menosVendidos->pluck('nombre')),
        datasets: [{
            label: 'Cantidad vendida',
            data: @json($menosVendidos->pluck('cantidad_vendida')),
            backgroundColor: COLOR_NARANJA,
            borderRadius: 4,
            maxBarThickness: 28,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: COLOR_GRID }, ticks: { color: COLOR_TICK, precision: 0 } },
            y: { grid: { display: false }, ticks: { color: COLOR_TICK } },
        }
    }
});
@endif
</script>
@endpush
