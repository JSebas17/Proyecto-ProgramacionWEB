{{-- Layout principal de Ayni: reemplaza a includes/header.php y footer.php.
     Las vistas hijas usan @extends('layouts.app') y rellenan @section('contenido'). --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('titulo', 'Ayni') · Ayni</title>

  {{-- Bootstrap 5 e íconos (CDN, licencia MIT) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  {{-- Estilos propios; asset() genera la URL correcta desde /public --}}
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
</head>
<body>
  {{-- ===== Barra de navegación ===== --}}
  <nav class="navbar navbar-expand-lg navbar-dark navbar-ayni">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('home') }}">
        <span class="brand-mark"><i class="bi bi-people-fill"></i></span> Ayni
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav me-auto">
          {{-- request()->routeIs() marca como activo el enlace de la página actual --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Buscar trabajo</a>
          </li>
          @if (auth()->guest() || auth()->user()->rol === 'trabajador')
  <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('publicar') ? 'active' : '' }}" href="{{ route('publicar') }}">Ofrecer mi servicio</a>
  </li>
@endif
          @if (auth()->user()?->rol === 'trabajador')
  <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('servicios.*') ? 'active' : '' }}" href="{{ route('servicios.index') }}">Mis servicios</a>
  </li>
@endif

@if (auth()->user()?->rol === 'admin')
  <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.panel') }}">Administración</a>
  </li>
@endif
        </ul>

        <div class="d-flex gap-2 py-2 py-lg-0 align-items-center">
          @auth
            {{-- Usuario con sesión: saludo y cierre de sesión (POST + CSRF) --}}
            <span class="text-white small">Hola, {{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="post" class="m-0">
              @csrf
              <button class="btn btn-outline-light btn-sm" type="submit">Salir</button>
            </form>
          @else
            {{-- Visitante --}}
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Ingresar</a>
            <a href="{{ route('registro') }}" class="btn btn-light btn-sm fw-semibold">Registrarme</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>

  {{-- ===== Mensajes flash de sesión (éxito / error) ===== --}}
  <div class="container mt-3">
    @if (session('mensaje'))
      <div class="alert alert-success mb-0" role="status">{{ session('mensaje') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger mb-0" role="alert">{{ session('error') }}</div>
    @endif
  </div>

  {{-- ===== Contenido de cada página ===== --}}
  @yield('contenido')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  {{-- Cada vista puede añadir sus scripts con @push('scripts') --}}
  @stack('scripts')
</body>
</html>