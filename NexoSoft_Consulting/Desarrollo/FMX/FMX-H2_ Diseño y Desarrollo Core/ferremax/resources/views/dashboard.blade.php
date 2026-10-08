@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
{{-- CUS-01 paso 10: mensaje de bienvenida con el nombre del usuario --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Bienvenido, {{ $user->nombre_usuario }}</h3>
        <p class="text-muted mb-0">
            Rol:
            <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-primary' }} text-uppercase">{{ $user->rol }}</span>
        </p>
    </div>
    <small class="text-muted">{{ now()->format('d/m/Y') }}</small>
</div>

<p class="text-muted">Seleccione una opción del menú para comenzar.</p>
@endsection
