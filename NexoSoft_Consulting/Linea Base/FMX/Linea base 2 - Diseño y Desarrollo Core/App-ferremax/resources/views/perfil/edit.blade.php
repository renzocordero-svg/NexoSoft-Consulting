@extends('layouts.app')
@section('title','Mi perfil')
@section('content')
<h3 class="fw-bold mb-4">Cambiar contraseña</h3>
<form method="POST" action="{{ route('perfil.update') }}" class="card border-0 shadow-sm p-4" style="max-width:600px">@csrf @method('PUT')
@foreach(['actual'=>'Contraseña actual','password'=>'Nueva contraseña','password_confirmation'=>'Confirmar nueva contraseña'] as $name=>$label)
<label class="form-label" for="{{ $name }}">{{ $label }}</label><input id="{{ $name }}" name="{{ $name }}" type="password" class="form-control mb-3" required autocomplete="{{ $name === 'actual' ? 'current-password' : 'new-password' }}">
@endforeach
<p class="text-muted">Usa al menos ocho caracteres y un número.</p><button class="btn btn-warning">Guardar contraseña</button></form>
@endsection
