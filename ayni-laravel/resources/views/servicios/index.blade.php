{{-- Panel del trabajador: lista de sus servicios con editar / desactivar. --}}
@extends('layouts.app')
@section('titulo', 'Mis servicios')

@section('contenido')
  <main class="container py-4 py-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="display-title fw-bold mb-0">Mis servicios</h1>
      <a href="{{ route('publicar') }}" class="btn btn-ayni">Publicar otro</a>
    </div>

    <div class="card-ayni shadow-sm p-3 table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr><th>Servicio</th><th>Distrito</th><th>Desde</th><th>Calificación</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
          @forelse ($servicios as $s)
            <tr>
              <td>{{ $s->titulo }}</td>
              <td>{{ $s->distrito?->nombre }}</td>
              <td>S/ {{ number_format($s->precio_desde, 0) }}</td>
              <td>
                @if ($s->calificaciones_count)
                  <i class="bi bi-star-fill stars"></i> {{ number_format($s->calificaciones_avg_puntuacion, 1) }}
                  <small class="text-secondary">({{ $s->calificaciones_count }})</small>
                @else
                  <span class="text-secondary">Nuevo</span>
                @endif
              </td>
              <td><span class="badge {{ $s->estado === 'activo' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $s->estado }}</span></td>
              <td class="text-end">
                <a href="{{ route('servicios.edit', $s) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                @if ($s->estado === 'activo')
                  {{-- data-confirmar lo lee el script de abajo (sin onclick en el HTML) --}}
                  <form action="{{ route('servicios.destroy', $s) }}" method="post" class="d-inline"
                        data-confirmar="¿Desactivar este servicio?">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">Desactivar</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-secondary">Aún no publicas ningún servicio.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </main>
@endsection

@push('scripts')
  <script>
    // Pide confirmación antes de enviar cualquier formulario marcado con data-confirmar
    document.querySelectorAll('form[data-confirmar]').forEach(form =>
      form.addEventListener('submit', e => {
        if (!confirm(form.dataset.confirmar)) e.preventDefault();
      }));
  </script>
@endpush