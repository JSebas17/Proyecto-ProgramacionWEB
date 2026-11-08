{{-- Página de inicio: buscador, categorías, filtros y tarjetas de trabajadores. --}}
@extends('layouts.app')

@section('titulo', 'Buscar trabajo')

@section('contenido')
  {{-- Franja de confianza --}}
  <div class="trust-strip">
    <div class="container py-2 d-flex flex-wrap justify-content-center justify-content-md-end gap-3 small">
      <span><i class="bi bi-graph-up-arrow"></i> 80% informalidad laboral en Cusco</span>
      <span><i class="bi bi-patch-check-fill"></i> Identidad verificada</span>
      <span><i class="bi bi-star-fill"></i> Calificación mutua</span>
    </div>
  </div>

  <main class="container py-4 py-lg-5">
    <header class="mb-4">
      <h1 class="display-title fw-bold">¿Qué trabajo necesitas en tu casa o negocio?</h1>
      <p class="text-secondary mb-0">Elige un oficio, ve quién está verificado y contáctalo directo.</p>
    </header>

    {{-- Un solo formulario GET: buscador, categorías y filtros (no necesita @csrf) --}}
    <form method="get" action="{{ route('home') }}" id="filtros">
      {{-- Buscador --}}
      <div class="card-ayni search-box shadow-sm p-2 mb-4" role="search">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-6">
            <label class="visually-hidden" for="q">Oficio o nombre</label>
            <input id="q" name="q" class="form-control" type="search"
                   placeholder="Ej. arreglar un tablero" value="{{ $filtros['q'] }}">
          </div>
          <div class="col-7 col-md-4">
            <label class="visually-hidden" for="distrito">Distrito</label>
            <select id="distrito" name="distrito" class="form-select">
              <option value="">Todos los distritos</option>
              @foreach ($distritos as $d)
                <option value="{{ $d->nombre }}" @selected($d->nombre === $filtros['distrito'])>{{ $d->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-5 col-md-2 d-grid"><button class="btn btn-ayni" type="submit">Buscar</button></div>
        </div>
      </div>

      {{-- Categorías: clic filtra; clic otra vez quita el filtro --}}
      <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-lg-6 mb-4">
        @foreach ($oficios as $o)
          @php $activa = ($o->nombre === $filtros['oficio']); @endphp
          <div class="col">
            <button type="submit" name="oficio" value="{{ $activa ? '' : $o->nombre }}"
                    class="cat-btn {{ $activa ? 'active' : '' }}">
              <i class="bi bi-{{ $iconos[$o->nombre] ?? 'tools' }}"></i>{{ $o->nombre }}
            </button>
          </div>
        @endforeach
      </div>

      {{-- Filtros rápidos: se envían al cambiar --}}
      <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <label class="chip {{ $filtros['verif'] ? 'active' : '' }}">
          <input type="checkbox" name="verif" value="1" hidden @checked($filtros['verif']) onchange="this.form.submit()">
          <i class="bi bi-check2"></i> Solo verificados
        </label>
        <label class="chip {{ $filtros['cuatro'] ? 'active' : '' }}">
          <input type="checkbox" name="cuatro" value="1" hidden @checked($filtros['cuatro']) onchange="this.form.submit()">
          <i class="bi bi-star-fill stars"></i> 4 o más
        </label>
        <span class="ms-auto small text-secondary">{{ $trabajadores->total() }} resultados</span>
      </div>
    </form>

    {{-- Tarjetas de trabajadores --}}
    <section class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-4" aria-live="polite">
      @forelse ($trabajadores as $t)
        @php
          $perfil = $t->perfilTrabajador;
          $nota   = $t->calificaciones_recibidas_avg_puntuacion;
        @endphp
        <div class="col">
          <article class="card-ayni shadow-sm p-3 h-100 d-flex flex-column">
            <div class="d-flex gap-3 align-items-center">
              <div class="avatar">{{ $t->iniciales }}</div>
              <div>
                {{-- {{ }} escapa el HTML automáticamente (protección XSS) --}}
                <h2 class="h6 mb-0">{{ $t->nombre_completo }}</h2>
                <small class="text-secondary">{{ $perfil?->oficio?->nombre }} · {{ $t->distrito?->nombre }}</small>
              </div>
            </div>

            @if ($t->verificado)
              <span class="badge badge-verified rounded-pill align-self-start mt-2">
                <i class="bi bi-patch-check-fill"></i> Identidad verificada
              </span>
            @endif

            <div class="d-flex justify-content-between my-3">
              @if ($t->calificaciones_recibidas_count > 0)
                <span>
                  <i class="bi bi-star-fill stars"></i> <strong>{{ number_format($nota, 1) }}</strong>
                  <small class="text-secondary">({{ $t->calificaciones_recibidas_count }})</small>
                </span>
              @else
                <span class="text-secondary">Nuevo</span>
              @endif
              <strong class="text-dark">
                {{ $perfil?->tarifa_referencial ? 'desde S/ ' . number_format($perfil->tarifa_referencial, 0) : 'a convenir' }}
              </strong>
            </div>

            <div class="d-flex gap-2 mt-auto">
              <a href="{{ route('trabajadores.show', $t->id) }}" class="btn btn-outline-secondary btn-sm flex-fill">Ver perfil</a>
              <a href="https://wa.me/{{ $t->whatsapp }}" target="_blank" rel="noopener"
                 class="btn btn-whatsapp btn-sm flex-fill"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            </div>
          </article>
        </div>
      @empty
        <p class="text-secondary">No hay resultados. Prueba quitando un filtro.</p>
      @endforelse
    </section>

    {{-- Paginación (Bootstrap 5) --}}
    <div class="mt-4">{{ $trabajadores->links() }}</div>

    {{-- Proceso en 3 pasos --}}
    <section class="card-ayni shadow-sm p-4 mt-5">
      <div class="row g-4">
        <div class="col-md-4 d-flex gap-3"><span class="step-num">1</span><div><strong>Busca</strong><div class="small text-secondary">Por oficio y distrito</div></div></div>
        <div class="col-md-4 d-flex gap-3"><span class="step-num">2</span><div><strong>Revisa</strong><div class="small text-secondary">Identidad verificada y reseñas</div></div></div>
        <div class="col-md-4 d-flex gap-3"><span class="step-num">3</span><div><strong>Contacta y califica</strong><div class="small text-secondary">Por WhatsApp, y ambos se califican</div></div></div>
      </div>
    </section>
  </main>
@endsection