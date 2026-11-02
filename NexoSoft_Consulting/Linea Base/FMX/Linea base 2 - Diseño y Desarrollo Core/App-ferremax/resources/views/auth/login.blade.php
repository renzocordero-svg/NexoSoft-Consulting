@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center"
     style="background: linear-gradient(135deg, #1a1a2e 0%, #212529 100%);">

    <div class="card shadow-lg border-0" style="width: 100%; max-width: 420px;">
        <div class="card-body p-5">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <i class="bi bi-wrench-adjustable-circle-fill text-warning" style="font-size: 3rem;"></i>
                <h2 class="fw-bold mt-2 mb-0">FERRE<span class="text-warning">MAX</span></h2>
                <p class="text-muted small">Sistema de Gestión de Almacén</p>
            </div>

            {{-- Alerta de bloqueo / error general --}}
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Usuario
                    </label>
                    <input
                        type="text"
                        id="nombre_usuario"
                        name="nombre_usuario"
                        class="form-control form-control-lg @error('nombre_usuario') is-invalid @enderror"
                        value="{{ old('nombre_usuario') }}"
                        placeholder="Ingresa tu usuario"
                        autocomplete="username"
                        autofocus
                        required
                    >
                    @error('nombre_usuario')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-lock me-1"></i>Contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label text-muted" for="remember">Mantener sesión iniciada</label>
                </div>

                <button type="submit" class="btn btn-warning btn-lg w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar
                </button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                Bloqueado tras 3 intentos fallidos (5 minutos)
            </p>
        </div>
    </div>
</div>
@endsection
