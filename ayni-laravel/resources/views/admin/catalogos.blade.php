{{-- Gestión de catálogos: oficios y distritos. --}}
@extends('layouts.app')
@section('titulo', 'Catálogos')

@section('contenido')
<main class="container py-4 py-lg-5">
  <h1 class="display-title fw-bold mb-3">Administración</h1>
  @include('admin._tabs')

  <div class="row g-4">
    {{-- Oficios --}}
    <div class="col-lg-6">
      <div class="card-ayni shadow-sm p-4">
        <h2 class="h5">Oficios</h2>
        <form method="post" action="{{ route('admin.catalogos.oficios.store') }}" class="row g-2 mb-3">
          @csrf
          <div class="col-5"><input name="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Nombre" value="{{ old('nombre') }}"></div>
          <div class="col-5"><input name="descripcion" class="form-control" placeholder="Descripción (opcional)"></div>
          <div class="col-2 d-grid"><button class="btn btn-ayni" type="submit">+</button></div>
          @error('nombre')<div class="text-danger small">{{ $message }}</div>@enderror
        </form>
        <ul class="list-group">
          @foreach ($oficios as $o)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>{{ $o->nombre }} <small class="text-secondary">({{ $o->perfiles_count }} trabajadores)</small></span>
              <form action="{{ route('admin.catalogos.oficios.destroy', $o) }}" method="post" data-confirmar="¿Eliminar este oficio?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" type="submit">Eliminar</button>
              </form>
            </li>
          @endforeach
        </ul>
      </div>
    </div>

    {{-- Distritos --}}
    <div class="col-lg-6">
      <div class="card-ayni shadow-sm p-4">
        <h2 class="h5">Distritos</h2>
        <form method="post" action="{{ route('admin.catalogos.distritos.store') }}" class="row g-2 mb-3">
          @csrf
          <div class="col-5"><input name="nombre" class="form-control" placeholder="Nombre"></div>
          <div class="col-5"><input name="provincia" class="form-control" placeholder="Provincia" value="Cusco"></div>
          <div class="col-2 d-grid"><button class="btn btn-ayni" type="submit">+</button></div>
        </form>
        <ul class="list-group">
          @foreach ($distritos as $d)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>{{ $d->nombre }} <small class="text-secondary">({{ $d->usuarios_count }} usuarios)</small></span>
              <form action="{{ route('admin.catalogos.distritos.destroy', $d) }}" method="post" data-confirmar="¿Eliminar este distrito?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" type="submit">Eliminar</button>
              </form>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('form[data-confirmar]').forEach(f =>
      f.addEventListener('submit', e => { if (!confirm(f.dataset.confirmar)) e.preventDefault(); }));
  </script>
@endpush