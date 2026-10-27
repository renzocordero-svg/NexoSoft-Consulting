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
        .badge-rol-admin   { background-color: #dc3545; }
        .badge-rol-vendedor { background-color: #0d6efd; }
    @media print { nav, .sidebar, .d-print-none, .btn, .pagination { display:none!important; } main { margin:0!important; width:100%!important; padding:0!important; } body { background:white; } .card { box-shadow:none!important; } }
@media(max-width:767px) { .sidebar { min-height:auto; width:100%!important; } }
</style>
    @stack('styles')
</head>
<body>

{{-- ── Navbar ─────────────────────────────────────────────────────────── --}}
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : '#' }}">
        <i class="bi bi-wrench-adjustable-circle-fill me-1"></i>
        FERRE<span>MAX</span>
    </a>
    @auth
    <div class="d-flex align-items-center gap-3">
        <span class="badge badge-rol-{{ auth()->user()->rol }} text-uppercase">
            {{ auth()->user()->rol }}
        </span>
        <span class="text-light small">
            <i class="bi bi-person-circle me-1"></i><a class="text-light" href="{{ \Illuminate\Support\Facades\Route::has('perfil.edit') ? route('perfil.edit') : '#' }}">{{ auth()->user()->nombre_usuario }}</a>
        </span>
        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : '#' }}" class="m-0">
            @csrf
            <button class="btn btn-sm btn-outline-light">
                <i class="bi bi-box-arrow-right me-1"></i>Salir
            </button>
        </form>
    </div>
    @endauth
</nav>

@auth
<div class="container-fluid">
    <div class="row">

        {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
        <nav class="col-md-2 sidebar py-3 px-2">
            <ul class="nav flex-column gap-1">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : '#' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                @if(auth()->user()->isAdmin())
                <li class="nav-item mt-2">
                    <small class="text-secondary px-3 text-uppercase" style="font-size:.7rem">Administración</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('productos.index') ? route('productos.index') : '#' }}">
                        <i class="bi bi-box-seam"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('usuarios.index') ? route('usuarios.index') : '#' }}">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('reportes.index') ? route('reportes.index') : '#' }}">
                        <i class="bi bi-bar-chart-line"></i> Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('movimientos.*') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('movimientos.index') ? route('movimientos.index') : '#' }}">
                        <i class="bi bi-arrow-left-right"></i> Movimientos Stock
                    </a>
                </li>
                @endif

                <li class="nav-item mt-2">
                    <small class="text-secondary px-3 text-uppercase" style="font-size:.7rem">Ventas</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ventas.create') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('ventas.create') ? route('ventas.create') : '#' }}">
                        <i class="bi bi-cart3"></i> Nueva Venta
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ventas.index') ? 'active' : '' }}"
                       href="{{ \Illuminate\Support\Facades\Route::has('ventas.index') ? route('ventas.index') : '#' }}">
                        <i class="bi bi-receipt"></i> Historial Ventas
                    </a>
                </li>

            </ul>
        </nav>

        {{-- ── Contenido principal ──────────────────────────────────────── --}}
        <main class="col-md-10 ms-sm-auto px-4 py-4">

            @if($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            {{-- Mensajes flash --}}
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
