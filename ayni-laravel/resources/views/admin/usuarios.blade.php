{{-- Gestión de usuarios: verificar identidad y activar/desactivar cuentas. --}}
@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('contenido')
<main class="container py-4 py-lg-5">
  <h1 class="display-title fw-bold mb-3">Administración</h1>
  @include('admin._tabs')

  <div class="d-flex gap-2 mb-3">
    <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-secondary btn-sm">Todos</a>
    <a href="{{ route('admin.usuarios', ['estado' => 'pendientes']) }}" class="btn btn-outline-secondary btn-sm">Pendientes de verificar</a>
  </div>

  <div class="card-ayni shadow-sm p-3 table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Nombre</th><th>Rol</th><th>DNI</th><th>Distrito</th><th>Verificado</th><th>Cuenta</th><th></th></tr></thead>
      <tbody>
        @forelse ($usuarios as $u)
          <tr>
            <td>
              {{ $u->nombre_completo }}
              <div class="small text-secondary">{{ $u->email }}</div>
            </td>
            <td>{{ ucfirst($u->rol) }}{{ $u->perfilTrabajador?->oficio ? ' · ' . $u->perfilTrabajador->oficio->nombre : '' }}</td>
            <td>{{ $u->dni ?? '—' }}</td>
            <td>{{ $u->distrito?->nombre }}</td>
            <td>
              <span class="badge {{ $u->verificado ? 'text-bg-success' : 'text-bg-warning' }}">
                {{ $u->verificado ? 'Sí' : 'Pendiente' }}
              </span>
            </td>
            <td><span class="badge {{ $u->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $u->activo ? 'Activa' : 'Desactivada' }}</span></td>
            <td class="text-end text-nowrap">
              <form action="{{ route('admin.usuarios.verificar', $u) }}" method="post" class="d-inline">
                @csrf @method('PATCH')
                <button class="btn btn-outline-secondary btn-sm" type="submit">{{ $u->verificado ? 'Quitar verificación' : 'Verificar' }}</button>
              </form>
              @if ($u->id !== auth()->id())
                <form action="{{ route('admin.usuarios.activar', $u) }}" method="post" class="d-inline"
                      data-confirmar="¿Cambiar el estado de esta cuenta?">
                  @csrf @method('PATCH')
                  <button class="btn btn-outline-danger btn-sm" type="submit">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-secondary">No hay usuarios con ese filtro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $usuarios->links() }}</div>
</main>
@endsection

@push('scripts')
  <script>
    // Confirmación antes de enviar formularios marcados con data-confirmar
    document.querySelectorAll('form[data-confirmar]').forEach(f =>
      f.addEventListener('submit', e => { if (!confirm(f.dataset.confirmar)) e.preventDefault(); }));
  </script>
@endpush