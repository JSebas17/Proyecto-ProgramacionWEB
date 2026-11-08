{{-- Resumen de métricas de impacto social. --}}
@extends('layouts.app')
@section('titulo', 'Administración')

@section('contenido')
<main class="container py-4 py-lg-5">
  <h1 class="display-title fw-bold mb-3">Administración</h1>
  @include('admin._tabs')

  <div class="row g-3 row-cols-2 row-cols-lg-4">
    @foreach ([
      ['Trabajadores registrados', $metricas['trabajadores']],
      ['Identidades verificadas', $metricas['verificados']],
      ['Pendientes de verificar', $metricas['pendientes']],
      ['Clientes', $metricas['clientes']],
      ['Servicios activos', $metricas['servicios']],
      ['Calificaciones', $metricas['calificaciones']],
      ['Promedio general', $metricas['promedio'] . ' ★'],
    ] as [$etiqueta, $valor])
      <div class="col">
        <div class="card-ayni shadow-sm p-3 h-100">
          <div class="h2 mb-0">{{ $valor }}</div>
          <small class="text-secondary">{{ $etiqueta }}</small>
        </div>
      </div>
    @endforeach
  </div>

  @if ($metricas['pendientes'] > 0)
    <a href="{{ route('admin.usuarios', ['estado' => 'pendientes']) }}" class="btn btn-ayni mt-4">
      Verificar {{ $metricas['pendientes'] }} trabajador(es) pendiente(s)
    </a>
  @endif
</main>
@endsection