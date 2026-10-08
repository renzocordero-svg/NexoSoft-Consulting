{{-- CUS-01 Iniciar sesión: interfaz de inicio de sesión --}}
@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center"
     style="background: linear-gradient(135deg, #1a1a2e 0%, #212529 100%);">

    <div class="card shadow-lg border-0" style="width: 100%; max-width: 420px;">
        <div class="card-body p-5">

            <div class="text-center mb-4">
                <i class="bi bi-wrench-adjustable-circle-fill text-warning" style="font-size: 3rem;"></i>
                <h2 class="fw-bold mt-2 mb-0">FERRE<span class="text-warning">MAX</span></h2>
                <p class="text-muted small">Sistema Web de Gestión para Ferretería</p>
            </div>

            {{-- CUS-02 paso 7: cierre de sesión correcto --}}
            @if(session('status'))
                <div class="alert alert-success py-2" role="status">
                    <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
                </div>
            @endif

            {{-- CUS-02 FA 3.2.2: sesión expirada por inactividad --}}
            @if(session('expirada'))
                <div class="alert alert-warning py-2" role="alert">
                    <i class="bi bi-clock-history me-1"></i>{{ session('expirada') }}
                </div>
            @endif

            {{-- Credenciales incorrectas / bloqueo / cuenta inactiva (FA 3.2.1 – 3.2.3) --}}
            @error('login')
                <div class="alert alert-danger py-2" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                </div>
            @enderror

            {{-- novalidate: la validación la hace el script de abajo para mostrar mensajes propios --}}
            <form id="form-login" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Nombre de usuario
                    </label>
                    <input
                        type="text"
                        id="nombre_usuario"
                        name="nombre_usuario"
                        class="form-control form-control-lg @error('nombre_usuario') is-invalid @enderror"
                        value="{{ old('nombre_usuario') }}"
                        placeholder="Ingresa tu usuario"
                        autocomplete="username"
                        maxlength="60"
                        @if(!old('nombre_usuario')) autofocus @endif
                        required
                    >
                    <div class="invalid-feedback">
                        @error('nombre_usuario'){{ $message }}@else Ingrese su nombre de usuario.@enderror
                    </div>
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
                        @if(old('nombre_usuario')) autofocus @endif
                        required
                    >
                    <div class="invalid-feedback">
                        @error('password'){{ $message }}@else Ingrese su contraseña.@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar
                </button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                El acceso se bloquea tras {{ \App\Http\Controllers\Auth\LoginController::MAX_INTENTOS }} intentos fallidos
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // FA 3.2.4: si falta un campo obligatorio, se marca junto al campo y no se envía la petición.
    (function () {
        const form = document.getElementById('form-login');
        const campos = form.querySelectorAll('input[required]');

        form.addEventListener('submit', function (e) {
            let valido = true;

            campos.forEach(function (input) {
                const vacio = input.value.trim() === '';
                input.classList.toggle('is-invalid', vacio);
                if (vacio && valido) {
                    input.focus();
                    valido = false;
                }
            });

            if (!valido) {
                e.preventDefault();
            }
        });

        campos.forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.value.trim() !== '') input.classList.remove('is-invalid');
            });
        });
    })();
</script>
@endpush
