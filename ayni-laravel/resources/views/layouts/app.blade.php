{{-- Layout principal de Ayni. Estructura igual a los wireframes:
     cabecera (marca + 3 cifras) -> barra de menú -> contenido -> pie de página.
     Las vistas hijas usan @extends('layouts.app') y rellenan @section('contenido'). --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#9A0000">
  <title>@yield('titulo', 'Ayni') · Ayni</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

  {{-- Bootstrap 5 e íconos (CDN, licencia MIT) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  {{-- Estilos propios; asset() genera la URL correcta desde /public --}}
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
  {{-- Tema andino (paleta de 4 colores): se carga después y solo sobrescribe colores/adornos --}}
  <link href="{{ asset('css/tema-andino.css') }}" rel="stylesheet">
  <link href="{{ asset('css/chatbot.css') }}" rel="stylesheet">
</head>
<body>
  @php $yo = auth()->user(); @endphp

  {{-- Salto para quien navega con teclado o lector de pantalla --}}
  <a class="visually-hidden-focusable position-absolute p-2 bg-white" href="#contenido">Saltar al contenido</a>

  {{-- ===== Cabecera: marca y cifras ===== --}}
  <header class="cabecera">
    <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
      <a class="marca" href="{{ route('home') }}" aria-label="Ayni, ir al inicio">
        <x-chakana class="marca-icono" />
        <span>
          <span class="marca-nombre d-block">Ayni</span>
          <span class="marca-lema d-block">Red de Empleabilidad para Trabajadores Independientes · Cusco</span>
        </span>
      </a>
      <ul class="cifras">
        <li><strong>80%</strong><span>informalidad<br>laboral en Cusco</span></li>
        <li><strong>✓</strong><span>identidad<br>verificada</span></li>
        <li><strong>★</strong><span>calificación<br>mutua</span></li>
      </ul>
    </div>
    <div class="tejido" role="presentation"></div>
  </header>

  {{-- ===== Barra de menú ===== --}}
  <div class="menu-barra">
    <div class="container">
      <nav class="navbar navbar-expand-lg" aria-label="Menú principal">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Abrir menú">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
          <ul class="navbar-nav me-auto">
            {{-- request()->routeIs() marca como activo el enlace de la página actual --}}
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('home', 'trabajadores.*', 'calificaciones.*') ? 'active' : '' }}"
                 href="{{ route('home') }}">Buscar trabajo</a>
            </li>
            {{-- Visitantes y trabajadores: /ofrecer los lleva a Registro o Publicar según corresponda --}}
            @if (! $yo || $yo->rol === 'trabajador')
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('ofrecer', 'publicar', 'servicios.*') ? 'active' : '' }}"
                   href="{{ route('ofrecer') }}">Ofrecer mi servicio</a>
              </li>
            @endif
            @if ($yo?->rol === 'trabajador')
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('servicios.index') ? 'active' : '' }}" href="{{ route('servicios.index') }}">Mis servicios</a>
              </li>
            @endif
            @if ($yo?->rol === 'admin')
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.panel') }}">Administración</a>
              </li>
            @endif
          </ul>

          @auth
            {{-- Cuenta: foto, nombre y menú desplegable --}}
            <div class="menu-cuenta dropdown">
              <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <x-avatar :user="$yo" size="sm" />
                <span>{{ $yo->name }}</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('perfil.edit') }}"><i class="bi bi-person-circle me-2"></i>Mi perfil y foto</a></li>
                @if ($yo->rol === 'trabajador')
                  <li><a class="dropdown-item" href="{{ route('trabajadores.show', $yo) }}"><i class="bi bi-eye me-2"></i>Ver mi perfil público</a></li>
                  <li><a class="dropdown-item" href="{{ route('servicios.index') }}"><i class="bi bi-briefcase me-2"></i>Mis servicios</a></li>
                @endif
                @if ($yo->rol === 'admin')
                  <li><a class="dropdown-item" href="{{ route('admin.panel') }}"><i class="bi bi-speedometer2 me-2"></i>Administración</a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                  {{-- Cerrar sesión: POST + CSRF --}}
                  <form action="{{ route('logout') }}" method="post" class="m-0">
                    @csrf
                    <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                  </form>
                </li>
              </ul>
            </div>
          @else
            <div class="d-flex align-items-center gap-3 py-2 py-lg-0">
              <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Ingresar</a>
              <a href="{{ route('registro') }}" class="btn btn-awayo btn-sm px-3">Registrarme</a>
            </div>
          @endauth
        </div>
      </nav>
    </div>
  </div>

  {{-- ===== Mensajes flash de sesión (éxito / error) ===== --}}
  @if (session('mensaje') || session('error'))
    <div class="container mt-3">
      @if (session('mensaje'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="status">
          <i class="bi bi-check-circle-fill me-1"></i> {{ session('mensaje') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar mensaje"></button>
        </div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar mensaje"></button>
        </div>
      @endif
    </div>
  @endif

  {{-- ===== Contenido de cada página ===== --}}
  <div id="contenido">
    @yield('contenido')
  </div>

  {{-- ===== Pie de página ===== --}}
  <footer class="pie">
    <div class="tejido" role="presentation"></div>
    <div class="container py-4">
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="d-flex align-items-center gap-2 mb-2">
            <x-chakana class="marca-icono" style="width:32px;height:32px" />
            <span class="marca-nombre">Ayni</span>
          </div>
          <p class="mb-0" style="max-width: 36rem">
            Ayni es la ayuda mutua andina. Esta red conecta a electricistas, pintores, gasfiteros y otros oficios
            de Cusco con clientes que quieren contratar con confianza.
          </p>
        </div>
        <div class="col-6 col-lg-3">
          <h2>Explorar</h2>
          <ul>
            <li><a href="{{ route('home') }}">Buscar trabajo</a></li>
            @if (! $yo || $yo->rol === 'trabajador')
              <li><a href="{{ route('ofrecer') }}">Ofrecer mi servicio</a></li>
            @endif
          </ul>
        </div>
        <div class="col-6 col-lg-3">
          <h2>Mi cuenta</h2>
          <ul>
            @auth
              <li><a href="{{ route('perfil.edit') }}">Mi perfil y foto</a></li>
              @if ($yo->rol === 'trabajador')<li><a href="{{ route('servicios.index') }}">Mis servicios</a></li>@endif
              @if ($yo->rol === 'admin')<li><a href="{{ route('admin.panel') }}">Administración</a></li>@endif
            @else
              <li><a href="{{ route('login') }}">Ingresar</a></li>
              <li><a href="{{ route('registro') }}">Registrarme</a></li>
            @endauth
          </ul>
        </div>
      </div>
      <div class="legal mt-4 pt-3">
        Proyecto académico · Universidad Continental · Programación Web (NRC 39577) · Cusco, 2026
      </div>
    </div>
  </footer>

  {{-- Botón para volver arriba (aparece al bajar la página; ver public/js/app.js) --}}
  <a href="#contenido" class="subir" id="subir" aria-label="Volver arriba" hidden><i class="bi bi-arrow-up" aria-hidden="true"></i></a>

  {{-- Chatbot de ayuda (no se muestra al administrador) --}}
  @if (auth()->user()?->rol !== 'admin')
    @include('partials.chatbot')
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  @if (auth()->user()?->rol !== 'admin')
    <script src="{{ asset('js/chatbot.js') }}"></script>
  @endif
  {{-- Cada vista puede añadir sus scripts con @push('scripts') --}}
  @stack('scripts')
</body>
</html>
