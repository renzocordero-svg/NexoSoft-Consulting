@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')

@php
    $esUsuarioActual = $usuario->id === auth()->id();
@endphp

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0">Editar Usuario</h3>
        <p class="text-muted mb-0 small">Deja la contraseña en blanco para mantener la actual.</p>
    </div>
</div>

<div class="card border-0 shadow-sm" style="max-width: 700px;">
    <div class="card-body p-4">

        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" novalidate>
            @csrf
            @method('PUT')

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
                           value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}"
                           maxlength="60" required>
                    @error('nombre_usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                    <select id="rol" name="rol" class="form-select @error('rol') is-invalid @enderror" required>
                        <option value="admin" {{ old('rol', $usuario->rol) === 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="vendedor" {{ old('rol', $usuario->rol) === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                    </select>
                    @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-lock me-1"></i>Contraseña
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           minlength="8" placeholder="Dejar en blanco para no cambiar">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Mínimo 8 caracteres y un número, si decides cambiarla.</div>
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control"
                           minlength="8">
                </div>
            </div>

            <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                <i class="bi bi-toggle-on me-1"></i>Estado
            </h6>
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="activo" name="activo"
                           {{ old('activo', $usuario->activo) ? 'checked' : '' }}
                           {{ $esUsuarioActual ? 'disabled' : '' }}>
                    <label class="form-check-label fw-semibold" for="activo">Usuario activo</label>
                </div>
                @if($esUsuarioActual)
                    <div class="form-text text-warning">
                        <i class="bi bi-info-circle"></i> No puedes desactivar tu propia cuenta.
                    </div>
                    {{-- El checkbox disabled no se envía; se fuerza el valor real para no perderlo. --}}
                    <input type="hidden" name="activo" value="1">
                @else
                    <div class="form-text">Los usuarios inactivos no pueden iniciar sesión.</div>
                @endif
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold px-4">
                    <i class="bi bi-floppy me-1"></i>Guardar cambios
                </button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-4">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
