@extends('layouts.app')

@section('title', 'Acceso denegado')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 60vh;">
    <i class="bi bi-shield-lock-fill text-danger" style="font-size: 5rem;"></i>
    <h2 class="fw-bold mt-3">Acceso denegado</h2>
    <p class="text-muted">No tienes permisos para ver esta sección.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary mt-2">
        <i class="bi bi-house me-1"></i>Volver al dashboard
    </a>
</div>
@endsection
