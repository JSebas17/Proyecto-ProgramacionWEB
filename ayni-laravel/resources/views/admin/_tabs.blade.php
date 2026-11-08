{{-- Pestañas de navegación del panel de administración. --}}
<ul class="nav nav-pills mb-4 gap-1">
  @foreach (['admin.panel' => 'Resumen', 'admin.usuarios' => 'Usuarios', 'admin.calificaciones' => 'Calificaciones', 'admin.catalogos' => 'Catálogos'] as $ruta => $texto)
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs($ruta) ? 'active' : '' }}" href="{{ route($ruta) }}">{{ $texto }}</a>
    </li>
  @endforeach
</ul>