{{-- Migas y pestañas de navegación del panel de administración. --}}
@php
  $pestanas = ['admin.panel' => 'Resumen', 'admin.usuarios' => 'Usuarios', 'admin.calificaciones' => 'Calificaciones', 'admin.catalogos' => 'Catálogos'];
  $actual = collect($pestanas)->first(fn ($texto, $ruta) => request()->routeIs($ruta), 'Resumen');
@endphp
<x-migas :items="[['Inicio', route('home')], ['Administración', route('admin.panel')], [$actual]]" />
<ul class="nav nav-pills mb-4 gap-1">
  @foreach ($pestanas as $ruta => $texto)
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs($ruta) ? 'active' : '' }}" href="{{ route($ruta) }}" @if (request()->routeIs($ruta)) aria-current="page" @endif>{{ $texto }}</a>
    </li>
  @endforeach
</ul>
