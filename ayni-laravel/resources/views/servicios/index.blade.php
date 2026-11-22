{{-- Panel del trabajador: lista de sus servicios con editar / desactivar. --}}
@extends('layouts.app')
@section('titulo', 'Mis servicios')

@section('contenido')
  @php $yo = auth()->user(); @endphp
  <main class="container py-4 py-lg-5">
    <x-migas :items="[['Inicio', route('home')], ['Mis servicios']]" />
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div class="d-flex align-items-center gap-3">
        <x-avatar :user="$yo" size="lg" :alt="'Tu foto de perfil'" />
        <div>
          <h1 class="display-title mb-1">Mis servicios</h1>
          <a href="{{ route('trabajadores.show', $yo) }}" class="small me-3">Ver mi perfil público</a>
          <a href="{{ route('perfil.edit') }}" class="small">Editar mi perfil y foto</a>
        </div>
      </div>
      <a href="{{ route('publicar') }}" class="btn btn-ayni"><i class="bi bi-plus-lg" aria-hidden="true"></i> Publicar otro</a>
    </div>

    @if ($servicios->isEmpty())
      <div class="card-ayni p-5 text-center">
        <p class="h5">Aún no publicas ningún servicio</p>
        <p class="text-secondary">Publica el primero y empieza a aparecer en las búsquedas de tu distrito.</p>
        <div><a href="{{ route('publicar') }}" class="btn btn-ayni px-4">Publicar mi servicio</a></div>
      </div>
    @else
      <div class="card-ayni p-3 table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr><th scope="col">Servicio</th><th scope="col">Distrito</th><th scope="col">Desde</th><th scope="col">Calificación</th><th scope="col">Estado</th><th scope="col"><span class="visually-hidden">Acciones</span></th></tr>
          </thead>
          <tbody>
            @foreach ($servicios as $s)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    @if (! empty($s->fotos))
                      <img src="{{ asset('storage/' . $s->fotos[0]) }}" alt="" width="48" height="48" class="rounded object-fit-cover" loading="lazy">
                    @else
                      <span class="photo-slot" style="width:48px;font-size:1.1rem"><i class="bi bi-image" aria-hidden="true"></i></span>
                    @endif
                    <span>{{ $s->titulo }}</span>
                  </div>
                </td>
                <td>{{ $s->distrito?->nombre }}</td>
                <td>S/ {{ number_format($s->precio_desde, 0) }}</td>
                <td>
                  @if ($s->calificaciones_count)
                    <i class="bi bi-star-fill stars" aria-hidden="true"></i> {{ number_format($s->calificaciones_avg_puntuacion, 1) }}
                    <small class="text-secondary">({{ $s->calificaciones_count }})</small>
                  @else
                    <span class="text-secondary">Nuevo</span>
                  @endif
                </td>
                <td><span class="badge {{ $s->estado === 'activo' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $s->estado === 'activo' ? 'Activo' : 'Inactivo' }}</span></td>
                <td class="text-end text-nowrap">
                  <a href="{{ route('servicios.edit', $s) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                  @if ($s->estado === 'activo')
                    {{-- data-confirmar lo lee public/js/app.js --}}
                    <form action="{{ route('servicios.destroy', $s) }}" method="post" class="d-inline" data-confirmar="¿Desactivar este servicio? Dejará de aparecer en las búsquedas.">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm" type="submit">Desactivar</button>
                    </form>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </main>
@endsection
