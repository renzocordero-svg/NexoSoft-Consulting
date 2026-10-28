@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

{{-- ── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-people me-2 text-warning"></i>Usuarios</h3>
        <p class="text-muted mb-0 small">Gestión de cuentas · Solo administradores</p>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn btn-warning fw-semibold">
        <i class="bi bi-plus-circle me-1"></i>Nuevo usuario
    </a>
</div>

{{-- ── Buscador ───────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label mb-1 small fw-semibold text-muted">Buscar por nombre de usuario</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        placeholder="Ej: jperez…"
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
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Tabla de usuarios ──────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        @if($usuarios->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:3rem;"></i>
                <p class="mt-3 mb-0">
                    @if($buscar)
                        No se encontraron usuarios para <strong>"{{ $buscar }}"</strong>.
                    @else
                        Aún no hay usuarios registrados.
                    @endif
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre de usuario</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha de creación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                        <tr>
                            <td class="text-muted small">{{ $usuario->id }}</td>
                            <td class="fw-semibold">
                                {{ $usuario->nombre_usuario }}
                                @if($usuario->id === auth()->id())
                                    <span class="badge bg-info text-dark ms-1">Tú</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-rol-{{ $usuario->rol }} text-uppercase">
                                    {{ $usuario->rol }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($usuario->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('usuarios.edit', $usuario) }}"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @unless($usuario->id === auth()->id())
                                    <form method="POST"
                                          action="{{ route('usuarios.destroy', $usuario) }}"
                                          class="form-toggle-estado d-inline">
                                        @csrf
                                        @method('DELETE')
                                        @if($usuario->activo)
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Desactivar" data-confirm="¿Desactivar a «{{ $usuario->nombre_usuario }}»? Podrás reactivarlo luego.">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                    title="Reactivar" data-confirm="¿Reactivar a «{{ $usuario->nombre_usuario }}»?">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        @endif
                                    </form>
                                    @endunless
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
                    Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }}
                    de {{ $usuarios->total() }} usuario(s)
                </small>
                {{ $usuarios->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.form-toggle-estado').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        if (confirm(btn.dataset.confirm)) {
            form.submit();
        }
    });
});
</script>
@endpush
