@extends('layouts.app')

@section('title', 'Nueva Venta')

@push('styles')
<style>
    #catalogoProductos { max-height: 480px; overflow-y: auto; }
    #carritoBody tr td { vertical-align: middle; }
    .qty-input { width: 80px; }
    .fila-producto:hover { background-color: #f8f9fa; cursor: pointer; }
    .en-carrito { background-color: #e8f5e9 !important; }
    #panelTotales { background: #f8f9fa; border-radius: 8px; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-primary"></i>Nueva Venta</h3>
        <p class="text-muted mb-0 small">Selecciona productos y confirma para generar la factura.</p>
    </div>
</div>

{{-- Alerta de error general (stock insuficiente, etc.) --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Alerta de stock insuficiente en el carrito (JS) --}}
<div id="alertaStock" class="alert alert-warning d-none d-print-none" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-1"></i>
    <span></span>
</div>

<div class="row g-4">

    {{-- ── PANEL IZQUIERDO: Catálogo de productos ───────────────────────── --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-semibold mb-2"><i class="bi bi-box-seam me-1"></i>Productos disponibles</h6>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="buscarProducto" class="form-control"
                           placeholder="Filtrar por nombre o categoría…" autocomplete="off">
                </div>
            </div>
            <div class="card-body p-0">
                @if($productos->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-exclamation-circle" style="font-size:2rem;"></i>
                        <p class="mt-2">No hay productos con stock disponible.</p>
                    </div>
                @else
                <div id="catalogoProductos">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $p)
                            <tr class="fila-producto {{ $p->cantidad_stock <= $p->stock_minimo ? 'table-warning' : '' }}"
                                id="fila-{{ $p->id }}"
                                data-nombre="{{ strtolower($p->nombre) }}"
                                data-categoria="{{ strtolower($p->categoria) }}">
                                <td>
                                    <div class="fw-semibold">{{ $p->nombre }}</div>
                                    <small class="text-muted">{{ $p->categoria }}</small>
                                    @if($p->cantidad_stock <= $p->stock_minimo)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Bajo mínimo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold {{ $p->cantidad_stock <= $p->stock_minimo ? 'text-warning' : 'text-success' }}">
                                    {{ $p->cantidad_stock }}
                                </td>
                                <td class="text-end">${{ number_format($p->precio_unitario, 2) }}</td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-agregar"
                                            id="btn-{{ $p->id }}"
                                            onclick="agregarProducto(
                                                {{ $p->id }},
                                                @js($p->nombre),
                                                {{ $p->precio_unitario }},
                                                {{ $p->cantidad_stock }},
                                                @js($p->categoria)
                                            )">
                                        <i class="bi bi-plus-circle"></i> Agregar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── PANEL DERECHO: Carrito + Totales + Confirmar ─────────────────── --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-receipt me-1"></i>Carrito de venta</h6>
                <span class="badge bg-primary" id="contadorItems">0 ítems</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="tablaCarrito">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center" style="width:110px;">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="carritoBody">
                            <tr id="filaVacia">
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-cart-x" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">El carrito está vacío.<br>
                                    <small>Haz clic en <strong>Agregar</strong> en los productos de la izquierda.</small></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Totales RF-11 --}}
            <div class="card-footer bg-white">
                <div id="panelTotales" class="p-3 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">$<span id="resSubtotal">0.00</span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">IGV (18%)</span>
                        <span class="fw-semibold">$<span id="resImpuestos">0.00</span></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-5">TOTAL</span>
                        <span class="fw-bold fs-5 text-primary">$<span id="resTotal">0.00</span></span>
                    </div>
                </div>

                {{-- Formulario que se envía al servidor --}}
                <form method="POST" action="{{ route('ventas.store') }}" id="formVenta">
                    @csrf
                    <div id="camposOcultos"></div>
                    <button type="submit" class="btn btn-success w-100 fw-semibold py-2"
                            id="btnConfirmar" disabled>
                        <i class="bi bi-check-circle me-1"></i>Confirmar venta y generar factura
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Estado del carrito ────────────────────────────────────────────────────
function escapar(texto) { const el=document.createElement('span'); el.textContent=texto; return el.innerHTML; }
const carrito = {}; // { id: { id, nombre, precio, stock, categoria, cantidad } }

// ── Agregar producto al carrito ───────────────────────────────────────────
function agregarProducto(id, nombre, precio, stock, categoria) {
    if (carrito[id]) {
        // Ya está en el carrito: enfocar el input de cantidad
        document.getElementById('qty-' + id)?.focus();
        return;
    }
    carrito[id] = { id, nombre, precio, stock, categoria, cantidad: 1 };
    renderCarrito();
}

// ── Quitar producto del carrito ───────────────────────────────────────────
function quitarProducto(id) {
    delete carrito[id];
    renderCarrito();
}

// ── Actualizar cantidad con validación RF-12 ──────────────────────────────
function cambiarCantidad(id, nuevaCantidad) {
    const item = carrito[id];
    if (!item) return;

    nuevaCantidad = parseInt(nuevaCantidad) || 1;

    if (nuevaCantidad < 1) nuevaCantidad = 1;

    if (nuevaCantidad > item.stock) {
        nuevaCantidad = item.stock;
        mostrarAlertaStock(item.nombre, item.stock);
    }

    carrito[id].cantidad = nuevaCantidad;

    // Actualizar el input sin re-renderizar toda la tabla (mejor UX)
    const input = document.getElementById('qty-' + id);
    if (input && parseInt(input.value) !== nuevaCantidad) {
        input.value = nuevaCantidad;
    }
    const celda=document.querySelector('.subtotal-linea-'+id);
    if(celda) celda.textContent='$'+(item.precio*nuevaCantidad).toFixed(2);
    actualizarTotales();
    actualizarCamposOcultos();
}

function mostrarAlertaStock(nombre, stockMax) {
    const alerta = document.getElementById('alertaStock');
    alerta.textContent = `Stock insuficiente para "${nombre}". Máximo disponible: ${stockMax}.`;
    alerta.classList.remove('d-none');
    setTimeout(() => alerta.classList.add('d-none'), 4000);
}

// ── Render completo del carrito ───────────────────────────────────────────
function renderCarrito() {
    const tbody = document.getElementById('carritoBody');
    const items = Object.values(carrito);
    const filaVacia = document.getElementById('filaVacia');

    // Limpiar filas dinámicas anteriores
    tbody.querySelectorAll('.fila-carrito').forEach(f => f.remove());

    if (items.length === 0) {
        filaVacia.style.display = '';
        document.getElementById('btnConfirmar').disabled = true;
        document.getElementById('contadorItems').textContent = '0 ítems';
        actualizarTotales();
        actualizarCamposOcultos();
        actualizarBotonesAgregar();
        return;
    }

    filaVacia.style.display = 'none';

    items.forEach(item => {
        const subtotalLinea = item.precio * item.cantidad;
        const tr = document.createElement('tr');
        tr.className = 'fila-carrito';
        tr.id = 'carrito-fila-' + item.id;
        tr.innerHTML = `
            <td>
                <div class="fw-semibold">${escapar(item.nombre)}</div>
                <small class="text-muted">${escapar(item.categoria)}</small>
            </td>
            <td class="text-center">
                <input type="number"
                       id="qty-${item.id}"
                       class="form-control form-control-sm qty-input mx-auto"
                       value="${item.cantidad}"
                       min="1"
                       max="${item.stock}"
                       onchange="cambiarCantidad(${item.id}, this.value)"
                       oninput="cambiarCantidad(${item.id}, this.value)">
                <small class="text-muted">Máx: ${item.stock}</small>
            </td>
            <td class="text-end">$${item.precio.toFixed(2)}</td>
            <td class="text-end fw-semibold subtotal-linea-${item.id}">
                $${subtotalLinea.toFixed(2)}
            </td>
            <td class="text-center">
                <button type="button" onclick="quitarProducto(${item.id})"
                        class="btn btn-sm btn-outline-danger" title="Quitar">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('contadorItems').textContent = `${items.length} ítem(s)`;
    document.getElementById('btnConfirmar').disabled = false;

    actualizarTotales();
    actualizarCamposOcultos();
    actualizarBotonesAgregar();
}

// ── Calcular y mostrar totales RF-11 ─────────────────────────────────────
function actualizarTotales() {
    const items   = Object.values(carrito);
    const subtotal = items.reduce((s, i) => s + i.precio * i.cantidad, 0);
    const impuestos = Math.round((subtotal + Number.EPSILON) * 18) / 100;
    const total     = subtotal + impuestos;

    document.getElementById('resSubtotal').textContent  = subtotal.toFixed(2);
    document.getElementById('resImpuestos').textContent = impuestos.toFixed(2);
    document.getElementById('resTotal').textContent     = total.toFixed(2);
}

// ── Construir hidden inputs para el POST ──────────────────────────────────
function actualizarCamposOcultos() {
    const container = document.getElementById('camposOcultos');
    container.innerHTML = '';
    Object.values(carrito).forEach((item, index) => {
        container.innerHTML += `
            <input type="hidden" name="items[${index}][producto_id]" value="${item.id}">
            <input type="hidden" name="items[${index}][cantidad]"    value="${item.cantidad}">
        `;
    });
}

// ── Marcar/desmarcar botones del catálogo ────────────────────────────────
function actualizarBotonesAgregar() {
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        const id = parseInt(btn.id.replace('btn-', ''));
        const enCarrito = !!carrito[id];
        btn.classList.toggle('btn-outline-primary', !enCarrito);
        btn.classList.toggle('btn-success', enCarrito);
        btn.innerHTML = enCarrito
            ? '<i class="bi bi-check-circle"></i> En carrito'
            : '<i class="bi bi-plus-circle"></i> Agregar';

        document.getElementById('fila-' + id)?.classList.toggle('en-carrito', enCarrito);
    });
}

// ── Filtrar catálogo por texto ────────────────────────────────────────────
document.getElementById('buscarProducto').addEventListener('input', function () {
    const termino = this.value.toLowerCase().trim();
    document.querySelectorAll('.fila-producto').forEach(fila => {
        const texto = fila.dataset.nombre + ' ' + fila.dataset.categoria;
        fila.style.display = texto.includes(termino) ? '' : 'none';
    });
});

// ── Confirmar antes de enviar ─────────────────────────────────────────────
document.getElementById('formVenta').addEventListener('submit', function (e) {
    const total = document.getElementById('resTotal').textContent;
    if (!confirm(`¿Confirmar la venta por un total de $${total}?\nEsta acción no se puede deshacer.`)) {
        e.preventDefault();
    } else {
        document.getElementById('btnConfirmar').disabled = true;
    }
});
const productosIniciales = @json($productos->keyBy('id'));
const itemsAnteriores = @json(old('items', []));
for (const item of itemsAnteriores) {
    const p=productosIniciales[item.producto_id];
    if(p) carrito[p.id]={id:p.id,nombre:p.nombre,precio:Number(p.precio_unitario),stock:p.cantidad_stock,categoria:p.categoria,cantidad:Number(item.cantidad)};
}
renderCarrito();
</script>
@endpush
