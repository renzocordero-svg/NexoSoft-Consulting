@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Nuevo Usuario</h3>
        <p class="text-muted mb-0 small">RF-26 · La contraseña se almacena cifrada.</p>
    </div>
</div>

<div class="card border-0 shadow-sm" style="max-width: 700px;">
    <div class="card-body p-4">

        <form method="POST" action="{{ route('usuarios.store') }}" novalidate>
            @csrf

            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-person me-1"></i>Datos de la cuenta
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label for="nombre_usuario" class="form-label fw-semibold">
                        Nombre de usuario <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario"
                           class="form-control @error('nombre_usuario') is-invalid @enderror"
                           value="{{ old('nombre_usuario') }}"
                           placeholder="Ej: jperez"
                           maxlength="60" required autofocus>
                    @error('nombre_usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                    <select id="rol" name="rol" class="form-select @error('rol') is-invalid @enderror" required>
                        <option value="" disabled {{ old('rol') ? '' : 'selected' }}>Seleccionar…</option>
                        <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="vendedor" {{ old('rol', 'vendedor') === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                    </select>
                    @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-lock me-1"></i>Contraseña
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">
                        Contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           minlength="8" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Mínimo 8 caracteres y un número.</div>
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        Confirmar contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control"
                           minlength="8" required>
                </div>
            </div>

            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-toggle-on me-1"></i>Estado
            </h6>
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="activo" name="activo"
                           {{ old('activo', '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="activo">Usuario activo</label>
                </div>
                <div class="form-text">Los usuarios inactivos no pueden iniciar sesión.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold px-4">
                    <i class="bi bi-floppy me-1"></i>Guardar usuario
                </button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-4">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
