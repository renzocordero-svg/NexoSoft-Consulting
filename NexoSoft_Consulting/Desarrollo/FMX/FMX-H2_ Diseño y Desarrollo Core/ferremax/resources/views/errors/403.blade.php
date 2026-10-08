@extends('layouts.app')

@section('title', 'Acceso denegado')

@section('content')
<div class="text-center py-5">
    <i class="bi bi-shield-lock text-danger" style="font-size: 4rem;"></i>
    <h3 class="fw-bold mt-3">Acceso denegado</h3>
    <p class="text-muted">{{ $exception->getMessage() ?: 'No tiene permiso para acceder a esta sección.' }}</p>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver al inicio
    </a>
</div>
@endsection
