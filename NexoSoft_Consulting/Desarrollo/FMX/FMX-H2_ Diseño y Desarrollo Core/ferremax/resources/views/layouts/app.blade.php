<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FERREMAX – @yield('title', 'Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .navbar-brand span { color: #ffc107; font-weight: 700; }
        .sidebar { min-height: calc(100vh - 56px); background: #212529; }
        .sidebar .nav-link { color: #adb5bd; border-radius: 6px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #343a40; color: #fff; }
        .sidebar .nav-link i { width: 20px; }
    </style>
    @stack('styles')
</head>
<body>

@auth
{{-- ── Barra superior ───────────────────────────────────────────────────── --}}
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ route('dashboard') }}">
        <i class="bi bi-wrench-adjustable-circle-fill me-1"></i>FERRE<span>MAX</span>
    </a>
    <div class="d-flex align-items-center gap-3">
        <span class="badge {{ auth()->user()->isAdmin() ? 'bg-danger' : 'bg-primary' }} text-uppercase">
            {{ auth()->user()->rol }}
        </span>
        <span class="text-light small">
            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nombre_usuario }}
        </span>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        {{-- ── Menú lateral según el rol (CUS-01 paso 9, opciones en config/menu.php) ── --}}
        <nav class="col-md-2 d-none d-md-block sidebar py-3 px-2">
            <ul class="nav flex-column gap-1">
                @foreach(config('menu') as $opcion)
                    @if(in_array(auth()->user()->rol, $opcion['roles'], true) && Route::has($opcion['ruta']))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($opcion['ruta']) ? 'active' : '' }}"
                               href="{{ route($opcion['ruta']) }}">
                                <i class="bi {{ $opcion['icono'] }}"></i> {{ $opcion['titulo'] }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        <main class="col-md-10 ms-sm-auto px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
