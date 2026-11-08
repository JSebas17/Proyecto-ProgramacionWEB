{{-- Formulario compartido de crear/editar servicio + vista previa en vivo.
     Variables: $servicio (null al crear), $accion (URL), $oficios, $distritos. --}}
@php
  // Valores iniciales: old() si hubo error, si no el servicio, si no el perfil del usuario
  $usuario = auth()->user();
  $oficioSel   = old('oficio_id', $usuario->perfilTrabajador?->oficio_id);
  $distritoSel = old('distrito_id', $servicio?->distrito_id ?? $usuario->distrito_id);
  $tarifaSel   = old('tarifa', $servicio?->precio_desde ? (int) $servicio->precio_desde : '');
  $descSel     = old('descripcion', $servicio?->descripcion);
@endphp

<main class="container py-4 py-lg-5">
  <div class="row g-4">
    <div class="col-lg-7">
      <h1 class="display-title fw-bold">{{ $servicio ? 'Edita tu servicio' : 'Publica tu servicio' }}</h1>
      <p class="text-secondary">Así te encuentran los clientes de tu distrito.</p>

      <form method="post" action="{{ $accion }}" enctype="multipart/form-data" class="card-ayni shadow-sm p-4" novalidate>
        @csrf
        {{-- Los navegadores solo envían GET/POST: PUT se simula con este campo oculto --}}
        @if ($servicio) @method('PUT') @endif

        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-semibold" for="oficio">Oficio</label>
            <select id="oficio" name="oficio_id" class="form-select @error('oficio_id') is-invalid @enderror">
              @foreach ($oficios as $o)
                <option value="{{ $o->id }}" @selected($oficioSel == $o->id)>{{ $o->nombre }}</option>
              @endforeach
            </select>
            @error('oficio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-semibold" for="distrito">Distrito donde trabajo</label>
            <select id="distrito" name="distrito_id" class="form-select @error('distrito_id') is-invalid @enderror">
              @foreach ($distritos as $d)
                <option value="{{ $d->id }}" @selected($distritoSel == $d->id)>{{ $d->nombre }}</option>
              @endforeach
            </select>
            @error('distrito_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="tarifa">Tarifa referencial (S/)</label>
            <input id="tarifa" name="tarifa" type="number" min="1" placeholder="60 por visita"
                   class="form-control @error('tarifa') is-invalid @enderror" value="{{ $tarifaSel }}">
            @error('tarifa')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="descripcion">Describe tu trabajo</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="4"
                      placeholder="Instalaciones, tableros y reparación de cortes…">{{ $descSel }}</textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="fotos">Fotos de tus trabajos (hasta 4, JPG/PNG/WEBP, máx. 3 MB)</label>
            <input id="fotos" name="fotos[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                   class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror">
            @error('fotos')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @error('fotos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror

            {{-- Fotos actuales (solo al editar) --}}
            @if ($servicio && $servicio->fotos)
              <div class="d-flex gap-2 mt-2">
                @foreach ($servicio->fotos as $f)
                  <img src="{{ asset('storage/' . $f) }}" alt="Foto actual" width="70" height="70" class="rounded object-fit-cover">
                @endforeach
              </div>
              <small class="text-secondary">Si subes fotos nuevas, reemplazan a las actuales.</small>
            @endif
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn btn-ayni px-4" type="submit">{{ $servicio ? 'Guardar cambios' : 'Publicar servicio' }}</button>
            <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </div>
      </form>
    </div>

    {{-- Vista previa en vivo: la rellena public/js/publicar.js --}}
    <aside class="col-lg-5">
      <div class="card-ayni shadow-sm p-4 sticky-side">
        <h2 class="h5 mb-3">Así se verá tu tarjeta</h2>
        <div class="card-ayni p-3">
          <div class="d-flex gap-3 align-items-center">
            <div class="avatar">{{ $usuario->iniciales }}</div>
            <div>
              <div class="fw-bold text-dark">{{ $usuario->nombre_completo }}</div>
              <small class="text-secondary" id="p-sub"></small>
            </div>
          </div>
          <p class="small text-secondary mt-2 mb-2" id="p-desc"></p>
          <div class="d-flex justify-content-between border-top pt-2">
            <span class="text-secondary">Nuevo</span>
            <strong class="text-dark" id="p-precio"></strong>
          </div>
        </div>
        <p class="note mt-3 mb-0">Tu tarifa es visible para todos, sin sorpresas.</p>
      </div>
    </aside>
  </div>
</main>

@push('scripts')
  <script src="{{ asset('js/publicar.js') }}"></script>
@endpush