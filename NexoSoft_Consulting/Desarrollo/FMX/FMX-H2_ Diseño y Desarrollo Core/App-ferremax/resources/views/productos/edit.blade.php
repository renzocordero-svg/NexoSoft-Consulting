@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Editar Producto</h3>
        <p class="text-muted mb-0 small">RF-06 · Si modificas la cantidad de stock, se registrará un movimiento de ajuste.</p>
    </div>
</div>

{{-- Alerta de stock bajo ─ RF-09/RF-20 --}}
@if($producto->cantidad_stock <= $producto->stock_minimo && $producto->activo)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>Stock bajo mínimo:</strong>
        Este producto tiene <strong>{{ $producto->cantidad_stock }}</strong> unidades,
        por debajo del mínimo establecido de <strong>{{ $producto->stock_minimo }}</strong>.
        Considera realizar una compra.
    </div>
</div>
@endif

<div class="card border-0 shadow-sm" style="max-width: 900px;">
    <div class="card-body p-4">

        <form method="POST" action="{{ route('productos.update', $producto) }}" novalidate>
            @csrf
            @method('PUT')

            {{-- ── Información básica ──────────────────────────────────── --}}
            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-info-circle me-1"></i>Información básica
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $producto->nombre) }}"
                           maxlength="150" required>
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                    <input type="text" id="categoria" name="categoria"
                           class="form-control @error('categoria') is-invalid @enderror"
                           value="{{ old('categoria', $producto->categoria) }}"
                           maxlength="80" required>
                    @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="proveedor" class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                    <input type="text" id="proveedor" name="proveedor"
                           class="form-control @error('proveedor') is-invalid @enderror"
                           value="{{ old('proveedor', $producto->proveedor) }}"
                           maxlength="150" required>
                    @error('proveedor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="ubicacion" class="form-label fw-semibold">Ubicación en almacén</label>
                    <input type="text" id="ubicacion" name="ubicacion"
                           class="form-control @error('ubicacion') is-invalid @enderror"
                           value="{{ old('ubicacion', $producto->ubicacion) }}"
                           maxlength="80">
                    @error('ubicacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ── Precio y stock ───────────────────────────────────────── --}}
            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-bar-chart me-1"></i>Precio y stock
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="precio_unitario" class="form-label fw-semibold">
                        Precio venta <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="precio_unitario" name="precio_unitario"
                               class="form-control @error('precio_unitario') is-invalid @enderror"
                               value="{{ old('precio_unitario', $producto->precio_unitario) }}"
                               step="0.01" min="0.01" max="999999.99" required>
                        @error('precio_unitario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="precio_costo" class="form-label fw-semibold">
                        Precio costo <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="precio_costo" name="precio_costo"
                               class="form-control @error('precio_costo') is-invalid @enderror"
                               value="{{ old('precio_costo', $producto->precio_costo) }}"
                               step="0.01" min="0" max="999999.99" required>
                        @error('precio_costo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Usado para calcular la ganancia en Reportes.</div>
                </div>

                <div class="col-md-3">
                    <label for="cantidad_stock" class="form-label fw-semibold">
                        Cantidad en stock <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="cantidad_stock" readonly aria-readonly="true"
                           class="form-control @error('cantidad_stock') is-invalid @enderror"
                           value="{{ old('cantidad_stock', $producto->cantidad_stock) }}"
                           min="0" required>
                    @error('cantidad_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        Valor actual: <strong>{{ $producto->cantidad_stock }}</strong>.
                        Registra entradas o salidas con el formulario de movimientos.
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="stock_minimo" class="form-label fw-semibold">
                        Stock mínimo <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="stock_minimo" name="stock_minimo"
                           class="form-control @error('stock_minimo') is-invalid @enderror"
                           value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                           min="0" required>
                    @error('stock_minimo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ── Estado ───────────────────────────────────────────────── --}}
            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-toggle-on me-1"></i>Estado
            </h6>
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="activo" name="activo"
                           {{ old('activo', $producto->activo) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="activo">Producto activo</label>
                </div>
                <div class="form-text">Los productos inactivos no aparecen al registrar ventas.</div>
            </div>

            {{-- ── Historial rápido ─────────────────────────────────────── --}}
            @if($producto->movimientosStock()->exists())
            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-clock-history me-1"></i>Últimos movimientos
            </h6>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th class="text-center">Cantidad</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($producto->movimientosStock()->with('usuario')->latest('fecha_hora')->take(5)->get() as $mov)
                        <tr>
                            <td class="small text-muted">{{ $mov->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $mov->tipo === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td class="small">{{ ucfirst($mov->motivo) }}</td>
                            <td class="text-center fw-semibold">{{ $mov->cantidad }}</td>
                            <td class="small">{{ $mov->usuario->nombre_usuario ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- ── Botones ──────────────────────────────────────────────── --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold px-4">
                    <i class="bi bi-floppy me-1"></i>Guardar cambios
                </button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary px-4">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>

<form method="POST" action="{{ route('productos.stock',$producto) }}" class="card border-0 shadow-sm p-4 mt-4">@csrf
<h5>Registrar movimiento de stock</h5><div class="row g-3">
<div class="col-md-3"><label class="form-label" for="tipo">Tipo</label><select id="tipo" name="tipo" class="form-select"><option value="entrada">Entrada</option><option value="salida">Salida</option></select></div>
<div class="col-md-3"><label class="form-label" for="cantidad">Cantidad</label><input id="cantidad" name="cantidad" type="number" min="1" max="999999" class="form-control" required></div>
<div class="col-md-3"><label class="form-label" for="motivo">Motivo</label><select id="motivo" name="motivo" class="form-select"><option value="compra">Compra</option><option value="ajuste">Ajuste</option></select></div>
<div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">Registrar movimiento</button></div>
</div></form>
@endsection
