{{-- Formulario de calificación (wireframe "Califica el trabajo"). --}}
@extends('layouts.app')
@section('titulo', 'Calificar trabajo')

@section('contenido')
@php
  $unico = $servicios->count() === 1 ? $servicios->first() : null;
  $fecha = now()->translatedFormat('j \d\e F');
@endphp
<main class="container py-4 py-lg-5">
  <x-migas :items="[['Inicio', route('home')], [$trabajador->nombre_completo, route('trabajadores.show', $trabajador)], ['Calificar']]" />

  <div class="row g-4 mt-1">
    <div class="col-lg-8">
      <h1 class="display-title mb-2">Califica el trabajo</h1>
      <p class="text-secondary">
        {{ $unico ? $unico->titulo . ' · ' : 'Trabajo realizado por ' }}{{ $trabajador->nombre_completo }} · {{ $fecha }}
      </p>

      <form method="post" action="{{ route('calificaciones.store', $trabajador) }}" class="card-ayni p-4" data-calificar data-bloquear novalidate>
        @csrf

        {{-- Servicio: select si hay varios, campo oculto si hay uno solo --}}
        @if ($servicios->count() > 1)
          <div class="mb-4">
            <label class="form-label fw-semibold" for="servicio_id">¿Qué servicio califica?</label>
            <select id="servicio_id" name="servicio_id" class="form-select @error('servicio_id') is-invalid @enderror">
              @foreach ($servicios as $s)
                <option value="{{ $s->id }}" @selected(old('servicio_id') == $s->id)>{{ $s->titulo }} · {{ $s->distrito?->nombre }}</option>
              @endforeach
            </select>
            @error('servicio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        @else
          <input type="hidden" name="servicio_id" value="{{ $servicios->first()->id }}">
          @error('servicio_id')<div class="alert alert-danger">{{ $message }}</div>@enderror
        @endif

        {{-- Estrellas: public/js/calificar.js las pinta y guarda el valor en #puntaje --}}
        <fieldset class="mb-4">
          <legend class="h6">Tu calificación</legend>
          <input type="hidden" name="puntaje" id="puntaje" value="{{ old('puntaje') }}">
          <div id="estrellas" role="radiogroup" aria-label="Calificación de 1 a 5 estrellas">
            @for ($i = 1; $i <= 5; $i++)
              <button type="button" class="star-btn" data-v="{{ $i }}"
                      aria-label="{{ $i }} estrella{{ $i > 1 ? 's' : '' }}">★</button>
            @endfor
          </div>
          <small class="text-secondary" id="estrellas-txt" aria-live="polite">Elige de 1 a 5 estrellas.</small>
          @error('puntaje')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </fieldset>

        {{-- Etiquetas rápidas --}}
        <fieldset class="mb-4">
          <legend class="h6">¿Qué tal fue?</legend>
          <div class="d-flex flex-wrap gap-2" id="chips">
            @foreach (['Puntual', 'Pagó lo acordado', 'Buen trato'] as $c)
              @php $on = in_array($c, old('chips', []), true); @endphp
              <label class="chip {{ $on ? 'active' : '' }}">
                <input type="checkbox" name="chips[]" value="{{ $c }}" hidden @checked($on)>{{ $c }}
              </label>
            @endforeach
          </div>
        </fieldset>

        <label class="form-label fw-semibold" for="comentario">Comentario (opcional)</label>
        <textarea id="comentario" name="comentario" class="form-control mb-1 @error('comentario') is-invalid @enderror"
                  rows="4" maxlength="500" placeholder="Excelente acabado, dejó todo limpio…">{{ old('comentario') }}</textarea>
        @error('comentario')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
        <div class="form-text mb-3">Máximo 500 caracteres. Sé respetuoso: el comentario es público.</div>

        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-ayni px-4 py-2" type="submit">Enviar calificación</button>
          <a href="{{ route('trabajadores.show', $trabajador) }}" class="btn btn-outline-secondary py-2">Cancelar</a>
        </div>
      </form>
    </div>

    <aside class="col-lg-4">
      <div class="card-ayni p-4 mb-3">
        <div class="d-flex align-items-center gap-3">
          <x-avatar :user="$trabajador" />
          <div>
            <div class="fw-bold text-dark">{{ $trabajador->nombre_completo }}</div>
            <small class="text-secondary">{{ $trabajador->perfilTrabajador?->oficio?->nombre }} · {{ $trabajador->distrito?->nombre }}</small>
          </div>
        </div>
      </div>
      <div class="card-ayni p-4">
        <h2 class="h5">Tu opinión cuenta</h2>
        <p class="text-secondary">Calificas un servicio concreto una sola vez. Así los próximos clientes eligen con más confianza.</p>
        <p class="note mb-0">Tu reseña aparece en el perfil del trabajador.</p>
      </div>
    </aside>
  </div>
</main>
@endsection

@push('scripts')
  <script src="{{ asset('js/calificar.js') }}"></script>
@endpush
