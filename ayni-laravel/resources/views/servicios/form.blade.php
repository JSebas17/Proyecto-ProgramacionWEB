{{-- Formulario compartido de crear/editar servicio + vista previa en vivo (wireframe "Publica tu servicio").
     Variables: $servicio (null al crear), $accion (URL), $oficios, $distritos. --}}
@php
  // Valores iniciales: old() si hubo error, si no el servicio, si no el perfil del usuario
  $usuario = auth()->user();
  $oficioSel   = old('oficio_id', $usuario->perfilTrabajador?->oficio_id);
  $distritoSel = old('distrito_id', $servicio?->distrito_id ?? $usuario->distrito_id);
  $tarifaSel   = old('tarifa', $servicio?->precio_desde ? (int) $servicio->precio_desde : ($usuario->perfilTrabajador?->tarifa_referencial ? (int) $usuario->perfilTrabajador->tarifa_referencial : ''));
  $descSel     = old('descripcion', $servicio?->descripcion ?? $usuario->perfilTrabajador?->descripcion);
  $erroresFotos = collect($errors->get('fotos.*'))->flatten()->merge($errors->get('fotos'))->unique()->values();
@endphp

<main class="container py-4 py-lg-5">
  <div class="row g-4">
    <div class="col-lg-7">
      <h1 class="display-title mb-2">{{ $servicio ? 'Edita tu servicio' : 'Publica tu servicio' }}</h1>
      <p class="text-secondary">Así te encuentran los clientes de tu distrito.</p>

      <form method="post" action="{{ $accion }}" enctype="multipart/form-data" class="card-ayni p-4" novalidate data-bloquear>
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
            <input id="tarifa" name="tarifa" type="number" min="1" inputmode="numeric" placeholder="60 por visita"
                   class="form-control @error('tarifa') is-invalid @enderror" value="{{ $tarifaSel }}">
            <div class="form-text">Es lo que verán los clientes. Sin sorpresas.</div>
            @error('tarifa')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="descripcion">Describe tu trabajo</label>
            <textarea id="descripcion" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="4" maxlength="1000"
                      placeholder="Instalaciones, tableros y reparación de cortes…">{{ $descSel }}</textarea>
            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Fotos de trabajos: 4 casillas. Las nuevas se dibujan con public/js/publicar.js --}}
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-baseline">
              <span class="form-label fw-semibold mb-2" id="etiqueta-fotos">Fotos de tus trabajos</span>
              <small class="text-secondary" id="contador-fotos" aria-live="polite"></small>
            </div>

            <div id="rejilla-fotos" class="upload-grid" aria-labelledby="etiqueta-fotos">
              {{-- Fotos que ya tenía el servicio (al editar). La "x" las marca para quitar al guardar --}}
              @foreach ($servicio?->fotos ?? [] as $f)
                <div class="upload-slot llena" data-existente>
                  <img src="{{ asset('storage/' . $f) }}" alt="Foto actual {{ $loop->iteration }}">
                  <input type="checkbox" name="quitar[]" value="{{ $f }}" hidden>
                  <button type="button" class="quitar" aria-label="Quitar la foto actual {{ $loop->iteration }}"><i class="bi bi-x-lg"></i></button>
                </div>
              @endforeach
            </div>

            <input id="fotos" name="fotos[]" type="file" multiple class="visually-hidden"
                   accept="image/jpeg,image/png,image/webp" aria-describedby="ayuda-fotos">
            <noscript><input name="fotos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="form-control mt-2"></noscript>

            <div class="form-text" id="ayuda-fotos">Hasta 4 fotos · JPG, PNG o WEBP · máx. 3 MB cada una. Se comprimen solas.</div>
            <div class="text-danger small mt-1" id="fotos-aviso" role="alert" hidden></div>
            @foreach ($erroresFotos as $mensaje)
              <div class="text-danger small mt-1" role="alert">{{ $mensaje }}</div>
            @endforeach
          </div>

          <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn btn-ayni px-4 py-2" type="submit">{{ $servicio ? 'Guardar cambios' : 'Publicar servicio' }}</button>
            <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary py-2">Cancelar</a>
          </div>
        </div>
      </form>
    </div>

    {{-- Vista previa en vivo: la rellena public/js/publicar.js --}}
    <aside class="col-lg-5">
      <div class="card-ayni p-4 sticky-side">
        <h2 class="h5 mb-3">Así se verá tu tarjeta</h2>
        <div class="tarjeta">
          <div class="d-flex gap-3 align-items-center">
            <x-avatar :user="$usuario" />
            <div>
              <h2>{{ $usuario->nombre_completo }}</h2>
              <div class="sub" id="p-sub"></div>
            </div>
          </div>
          @if ($usuario->verificado)
            <span class="badge badge-verified rounded-pill align-self-start mt-2"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Identidad verificada</span>
          @endif
          <p class="small text-secondary mt-2 mb-2" id="p-desc"></p>
          <div class="d-flex justify-content-between border-top pt-2 mt-auto">
            <span class="text-secondary">Nuevo</span>
            <span class="precio" id="p-precio"></span>
          </div>
        </div>
        @unless ($usuario->foto)
          <p class="note mt-3 mb-0">
            <i class="bi bi-camera" aria-hidden="true"></i>
            Tu tarjeta no tiene foto. <a href="{{ route('perfil.edit') }}">Agrega tu foto de perfil</a> para generar más confianza.
          </p>
        @else
          <p class="note mt-3 mb-0">Tu tarifa es visible para todos, sin sorpresas.</p>
        @endunless
        <a class="small d-inline-block mt-3" href="{{ route('trabajadores.show', $usuario) }}">Ver mi perfil público</a>
      </div>
    </aside>
  </div>
</main>

@push('scripts')
  <script src="{{ asset('js/publicar.js') }}"></script>
@endpush
