{{-- Moderación de calificaciones. --}}
@extends('layouts.app')
@section('titulo', 'Moderar calificaciones')

@section('contenido')
<main class="container py-4 py-lg-5">
  <h1 class="display-title fw-bold mb-3">Administración</h1>
  @include('admin._tabs')

  <div class="card-ayni shadow-sm p-3 table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Autor</th><th>Trabajador</th><th>Puntos</th><th>Comentario</th><th>Fecha</th><th></th></tr></thead>
      <tbody>
        @forelse ($calificaciones as $c)
          <tr>
            <td>{{ $c->autor->nombre_completo }}</td>
            <td>{{ $c->servicio->trabajador->nombre_completo }}</td>
            <td class="stars">{{ str_repeat('★', $c->puntuacion) }}</td>
            <td>{{ $c->comentario ?: '—' }}</td>
            <td class="small text-secondary">{{ $c->created_at->format('d/m/Y') }}</td>
            <td class="text-end">
              <form action="{{ route('admin.calificaciones.destroy', $c) }}" method="post" data-confirmar="¿Eliminar esta calificación?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-secondary">No hay calificaciones.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $calificaciones->links() }}</div>
</main>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('form[data-confirmar]').forEach(f =>
      f.addEventListener('submit', e => { if (!confirm(f.dataset.confirmar)) e.preventDefault(); }));
  </script>
@endpush