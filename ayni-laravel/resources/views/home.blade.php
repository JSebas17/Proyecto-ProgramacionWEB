{{-- Página de inicio: buscador, categorías, filtros y tarjetas de trabajadores (wireframe "Buscar trabajo"). --}}
@extends('layouts.app')

@section('titulo', 'Buscar trabajo')

@section('contenido')
  {{-- Cuando la persona no está filtrando, se muestra el héroe con el logo grande y el carrusel --}}
  @php
    $buscando = $filtros['q'] !== '' || $filtros['distrito'] !== '' || $filtros['oficio'] !== '' || $filtros['cuatro']
                || $trabajadores->currentPage() > 1;
  @endphp
  @unless ($buscando)
    @include('partials.hero-inicio')
  @endunless

  <main class="container py-4 py-lg-5" id="buscar">
    <header class="mb-4">
      <h1 class="display-title mb-2">¿Qué trabajo necesitas en tu casa o negocio?</h1>
      <p class="text-secondary mb-0">Elige un oficio, ve quién está verificado y contáctalo directo.</p>
    </header>

    {{-- Un solo formulario GET: buscador, categorías, filtros y orden (no necesita @csrf) --}}
    <form method="get" action="{{ route('home') }}" id="filtros">
      {{-- Conserva la categoría elegida al presionar "Buscar" (el botón de categoría, más abajo, la reemplaza) --}}
      <input type="hidden" name="oficio" value="{{ $filtros['oficio'] }}">

      {{-- Buscador: texto + distrito --}}
      <div class="card-ayni search-box p-2 mb-4" role="search">
        <div class="row g-0 align-items-center">
          <div class="col-12 col-md-6 campo">
            <label class="visually-hidden" for="q">Oficio o nombre</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input id="q" name="q" class="form-control" type="search"
                   placeholder="Ej. arreglar un tablero" value="{{ $filtros['q'] }}">
          </div>
          <div class="col-8 col-md-4 campo">
            <label class="visually-hidden" for="distrito">Distrito</label>
            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
            <select id="distrito" name="distrito" class="form-select">
              <option value="">Todos los distritos</option>
              @foreach ($distritos as $d)
                <option value="{{ $d->nombre }}" @selected($d->nombre === $filtros['distrito'])>{{ $d->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-4 col-md-2 d-grid ps-2"><button class="btn btn-ayni py-2" type="submit">Buscar</button></div>
        </div>
      </div>

      {{-- Categorías: clic filtra; clic otra vez quita el filtro --}}
      <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-lg-6 mb-4">
        @foreach ($oficios as $o)
          @php $activa = ($o->nombre === $filtros['oficio']); @endphp
          <div class="col">
            <button type="submit" name="oficio" value="{{ $activa ? '' : $o->nombre }}"
                    class="cat-btn {{ $activa ? 'active' : '' }}" @if ($activa) aria-pressed="true" @endif>
              <i class="bi bi-{{ $iconos[$o->nombre] ?? 'tools' }}" aria-hidden="true"></i>{{ $o->nombre }}
            </button>
          </div>
        @endforeach
      </div>

      {{-- Filtros rápidos y orden: se envían al cambiar --}}
      <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        {{-- El 0 oculto hace que "desmarcar" también viaje en la URL (si no, "solo verificados" se reactivaría solo) --}}
        <input type="hidden" name="verif" value="0">
        <label class="chip {{ $filtros['verif'] ? 'active' : '' }}">
          <input type="checkbox" name="verif" value="1" hidden data-autoenviar @checked($filtros['verif'])>
          <i class="bi bi-check2" aria-hidden="true"></i> Solo verificados
        </label>
        <label class="chip {{ $filtros['cuatro'] ? 'active' : '' }}">
          <input type="checkbox" name="cuatro" value="1" hidden data-autoenviar @checked($filtros['cuatro'])>
          <i class="bi bi-star-fill {{ $filtros['cuatro'] ? '' : 'stars' }}" aria-hidden="true"></i> 4 o más
        </label>
        <div>
          <label class="visually-hidden" for="orden">Ordenar por</label>
          <select id="orden" name="orden" class="form-select chip-select" data-autoenviar>
            @foreach ($ordenes as $clave => $texto)
              <option value="{{ $clave }}" @selected($filtros['orden'] === $clave)>{{ $texto }}</option>
            @endforeach
          </select>
        </div>
        <span class="ms-auto small text-secondary" aria-live="polite">
          {{ $trabajadores->total() }} {{ $trabajadores->total() === 1 ? 'resultado' : 'resultados' }}
          en {{ $filtros['distrito'] ?: 'Cusco' }}
        </span>
      </div>
    </form>

    {{-- Tarjetas de trabajadores --}}
    <section class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-4" aria-label="Resultados de la búsqueda">
      @forelse ($trabajadores as $t)
        @php
          $perfil = $t->perfilTrabajador;
          $nota   = $t->calificaciones_recibidas_avg_puntuacion;
        @endphp
        <div class="col">
          <article class="tarjeta">
            <div class="d-flex gap-3 align-items-center">
              <x-avatar :user="$t" />
              <div class="min-w-0">
                {{-- {{ }} escapa el HTML automáticamente (protección XSS) --}}
                <h2><a href="{{ route('trabajadores.show', $t) }}" class="text-decoration-none">{{ $t->nombre_completo }}</a></h2>
                <div class="sub">{{ $perfil?->oficio?->nombre }} · {{ $t->distrito?->nombre }}</div>
              </div>
            </div>

            <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
              @if ($t->verificado)
                <span class="badge badge-verified rounded-pill">
                  <i class="bi bi-patch-check-fill" aria-hidden="true"></i> Identidad verificada
                </span>
              @endif
              @if ($perfil && ! $perfil->disponible)
                <span class="estado-ocupado"><i class="bi bi-clock" aria-hidden="true"></i> Ocupado ahora</span>
              @endif
            </div>

            <div class="d-flex justify-content-between align-items-baseline my-3">
              @if ($t->calificaciones_recibidas_count > 0)
                <span>
                  <i class="bi bi-star-fill stars" aria-hidden="true"></i> <strong>{{ number_format($nota, 1) }}</strong>
                  <small class="text-secondary">({{ $t->calificaciones_recibidas_count }})</small>
                </span>
              @else
                <span class="text-secondary">Nuevo</span>
              @endif
              <span class="precio">
                {{ $perfil?->tarifa_referencial ? 'desde S/ ' . number_format($perfil->tarifa_referencial, 0) : 'a convenir' }}
              </span>
            </div>

            <div class="d-flex gap-2 mt-auto">
              <a href="{{ route('trabajadores.show', $t) }}" class="btn btn-outline-secondary btn-sm flex-fill">Ver perfil</a>
              @if ($t->whatsapp_url)
                <a href="{{ $t->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm flex-fill">
                  <i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp
                </a>
              @endif
            </div>
          </article>
        </div>
      @empty
        <div class="col-12">
          <div class="card-ayni p-4 text-center">
            <p class="h5 mb-1">No encontramos trabajadores con esos filtros</p>
            <p class="text-secondary">Prueba con otro distrito o quita algún filtro.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
              <a href="{{ route('home') }}" class="btn btn-ayni btn-sm">Quitar filtros</a>
              @if ($filtros['verif'])
                <a href="{{ route('home', array_filter(['q' => $filtros['q'], 'distrito' => $filtros['distrito'], 'oficio' => $filtros['oficio']]) + ['verif' => 0]) }}"
                   class="btn btn-outline-secondary btn-sm">Incluir no verificados</a>
              @endif
            </div>
          </div>
        </div>
      @endforelse
    </section>

    {{-- Paginación (Bootstrap 5) --}}
    <div class="mt-4">{{ $trabajadores->links() }}</div>

    {{-- Carrusel de trabajadores destacados (solo en la portada, sin filtros) --}}
    @if (! $buscando && $trabajadores->count() >= 3)
      <section class="mt-5" id="destacados" data-carrusel-cards aria-label="Trabajadores destacados">
        <div class="seccion-titulo">
          <div>
            <h2>Trabajadores destacados</h2>
            <p>Los mejor valorados de la red · desliza para ver más</p>
          </div>
          <div class="car-flechas">
            <button type="button" data-prev aria-label="Anteriores"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button>
            <button type="button" data-next aria-label="Siguientes"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
          </div>
        </div>
        <div class="car-track">
          @foreach ($trabajadores->take(8) as $t)
            @php $perfil = $t->perfilTrabajador; @endphp
            <article class="tarjeta">
              <span class="destacado-pos" aria-hidden="true">{{ $loop->iteration }}</span>
              <div class="d-flex gap-3 align-items-center mt-2">
                <x-avatar :user="$t" />
                <div class="min-w-0">
                  <h3 class="h6 mb-0"><a href="{{ route('trabajadores.show', $t) }}" class="text-decoration-none">{{ $t->nombre_completo }}</a></h3>
                  <div class="sub">{{ $perfil?->oficio?->nombre }} · {{ $t->distrito?->nombre }}</div>
                </div>
              </div>
              <div class="d-flex justify-content-between align-items-baseline my-3">
                @if ($t->calificaciones_recibidas_count > 0)
                  <span><i class="bi bi-star-fill stars" aria-hidden="true"></i> <strong>{{ number_format($t->calificaciones_recibidas_avg_puntuacion, 1) }}</strong>
                    <small class="text-secondary">({{ $t->calificaciones_recibidas_count }})</small></span>
                @else
                  <span class="text-secondary">Nuevo</span>
                @endif
                <span class="precio">{{ $perfil?->tarifa_referencial ? 'desde S/ ' . number_format($perfil->tarifa_referencial, 0) : 'a convenir' }}</span>
              </div>
              <a href="{{ route('trabajadores.show', $t) }}" class="btn btn-ayni btn-sm mt-auto">Ver perfil</a>
            </article>
          @endforeach
        </div>
      </section>
    @endif

    {{-- Proceso en 3 pasos (sí es una secuencia, por eso van numerados) --}}
    <section class="card-ayni p-4 mt-5" aria-label="Cómo funciona">
      <div class="row g-4">
        <div class="col-md-4 d-flex gap-3"><span class="step-num">1</span><div><strong>Busca</strong><div class="small text-secondary">Por oficio y distrito</div></div></div>
        <div class="col-md-4 d-flex gap-3"><span class="step-num">2</span><div><strong>Revisa</strong><div class="small text-secondary">Identidad verificada y reseñas</div></div></div>
        <div class="col-md-4 d-flex gap-3"><span class="step-num">3</span><div><strong>Contacta y califica</strong><div class="small text-secondary">Por WhatsApp, y ambos se califican</div></div></div>
      </div>
    </section>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('js/inicio.js') }}"></script>
@endpush
