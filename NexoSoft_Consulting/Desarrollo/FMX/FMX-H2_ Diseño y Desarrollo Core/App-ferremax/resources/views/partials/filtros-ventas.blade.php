<form method="GET" class="card border-0 shadow-sm p-3 mb-4 d-print-none">
<div class="row g-3 align-items-end">
<div class="col-md-3"><label for="fecha_desde" class="form-label">Desde</label><input id="fecha_desde" name="fecha_desde" type="date" class="form-control" value="{{ $filtros['fecha_desde']??'' }}"></div>
<div class="col-md-3"><label for="fecha_hasta" class="form-label">Hasta</label><input id="fecha_hasta" name="fecha_hasta" type="date" class="form-control" value="{{ $filtros['fecha_hasta']??'' }}"></div>
@if(auth()->user()->isAdmin())<div class="col-md-3"><label for="usuario_id" class="form-label">Vendedor</label><select id="usuario_id" name="usuario_id" class="form-select"><option value="">Todos</option>@foreach($usuarios as $u)<option value="{{ $u->id }}" @selected((string)($filtros['usuario_id']??'')===(string)$u->id)>{{ $u->nombre_usuario }}</option>@endforeach</select></div>@endif
<div class="col-md-3"><button class="btn btn-primary">Filtrar</button> <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Limpiar</a></div>
</div></form>
