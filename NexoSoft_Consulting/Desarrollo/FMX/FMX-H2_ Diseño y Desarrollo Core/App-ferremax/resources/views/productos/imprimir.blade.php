@extends('layouts.app')
@section('title','Lista de existencias')
@section('content')
<h3>FERREMAX · Lista de existencias</h3><p>{{ now()->format('d/m/Y H:i') }}</p>
<button type="button" onclick="window.print()" class="btn btn-warning mb-3 d-print-none">Imprimir / Guardar PDF</button>
<table class="table table-bordered"><thead><tr><th>Producto</th><th>Categoría</th><th>Stock</th><th>Mínimo</th><th>Precio</th><th>Ubicación</th></tr></thead><tbody>
@foreach($productos as $p)<tr><td>{{ $p->nombre }}</td><td>{{ $p->categoria }}</td><td>{{ $p->cantidad_stock }}</td><td>{{ $p->stock_minimo }}</td><td>${{ number_format($p->precio_unitario,2) }}</td><td>{{ $p->ubicacion }}</td></tr>@endforeach
</tbody></table>
@endsection
