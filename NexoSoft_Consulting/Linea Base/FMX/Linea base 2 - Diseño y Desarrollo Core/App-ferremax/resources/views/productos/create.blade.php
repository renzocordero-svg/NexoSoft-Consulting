@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Nuevo Producto</h3>
        <p class="text-muted mb-0 small">La cantidad inicial generará un movimiento de entrada automático.</p>
    </div>
</div>

<div class="card border-0 shadow-sm" style="max-width: 900px;">
    <div class="card-body p-4">

        <form method="POST" action="{{ route('productos.store') }}" novalidate>
            @csrf

            {{-- ── Información básica ──────────────────────────────────── --}}
            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-info-circle me-1"></i>Información básica
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}"
                           placeholder="Ej: Martillo de Carpintero 16oz"
                           maxlength="150" required>
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                    <input type="text" id="categoria" name="categoria"
                           class="form-control @error('categoria') is-invalid @enderror"
                           value="{{ old('categoria') }}"
                           placeholder="Ej: Herramientas"
                           maxlength="80" required>
                    @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="proveedor" class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                    <input type="text" id="proveedor" name="proveedor"
                           class="form-control @error('proveedor') is-invalid @enderror"
                           value="{{ old('proveedor') }}"
                           placeholder="Ej: Stanley Tools"
                           maxlength="150" required>
                    @error('proveedor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="ubicacion" class="form-label fw-semibold">Ubicación en almacén</label>
                    <input type="text" id="ubicacion" name="ubicacion"
                           class="form-control @error('ubicacion') is-invalid @enderror"
                           value="{{ old('ubicacion') }}"
                           placeholder="Ej: Estante A1"
                           maxlength="80">
                    @error('ubicacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- ── Precios y stock ──────────────────────────────────────── --}}
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
                               value="{{ old('precio_unitario') }}"
                               step="0.01" min="0.01" max="999999.99"
                               placeholder="0.00" required>
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
                               value="{{ old('precio_costo') }}"
                               step="0.01" min="0" max="999999.99"
                               placeholder="0.00" required>
                        @error('precio_costo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Usado para calcular la ganancia en Reportes.</div>
                </div>

                <div class="col-md-3">
                    <label for="cantidad_stock" class="form-label fw-semibold">
                        Cantidad inicial <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="cantidad_stock" name="cantidad_stock"
                           class="form-control @error('cantidad_stock') is-invalid @enderror"
                           value="{{ old('cantidad_stock', 0) }}"
                           min="0" required>
                    @error('cantidad_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        <i class="bi bi-info-circle text-primary"></i>
                        Si es &gt; 0, se registrará un movimiento de <strong>entrada</strong> automáticamente.
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="stock_minimo" class="form-label fw-semibold">
                        Stock mínimo <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="stock_minimo" name="stock_minimo"
                           class="form-control @error('stock_minimo') is-invalid @enderror"
                           value="{{ old('stock_minimo', 0) }}"
                           min="0" required>
                    @error('stock_minimo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        Se mostrará alerta cuando el stock llegue a este nivel.
                    </div>
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
                           {{ old('activo', '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="activo">Producto activo</label>
                </div>
                <div class="form-text">Los productos inactivos no aparecen al registrar ventas.</div>
            </div>

            {{-- ── Botones ──────────────────────────────────────────────── --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold px-4">
                    <i class="bi bi-floppy me-1"></i>Guardar producto
                </button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary px-4">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
