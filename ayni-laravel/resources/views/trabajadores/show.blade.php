{{-- Perfil público del trabajador (wireframe "Perfil"). --}}
@extends('layouts.app')
@section('titulo', $trabajador->nombre_completo)

@section('contenido')
@php
  $perfil   = $trabajador->perfilTrabajador;
  $nota     = $trabajador->calificaciones_recibidas_avg_puntuacion;
  $total    = $trabajador->calificaciones_recibidas_count;
  $tarifa   = $perfil?->tarifa_referencial ? 'S/ ' . number_format($perfil->tarifa_referencial, 0) : 'A convenir';
  $anios    = (int) ($perfil?->anios_experiencia ?? 0);
  $esMio    = auth()->id() === $trabajador->id;
  $puedeCalificar = ! $esMio && auth()->user()?->rol !== 'admin';
@endphp
<main class="container py-4 py-lg-5">
  <x-migas :items="[['Inicio', route('home')], [$trabajador->nombre_completo]]" />

  {{-- Accesos dentro de la página --}}
  <nav class="subnav" aria-label="Secciones del perfil">
    <a href="#sobre-mi">Sobre mi trabajo</a>
    <a href="#trabajos">Trabajos</a>
    <a href="#resenas">Reseñas @if ($total > 0)<span>({{ $total }})</span>@endif</a>
  </nav>

  <div class="row g-4 mt-1">
    <div class="col-lg-8">
      <header class="d-flex align-items-center gap-3 gap-sm-4 mb-4">
        <x-avatar :user="$trabajador" size="lg" :alt="'Foto de ' . $trabajador->nombre_completo" />
        <div>
          <h1 class="h2 mb-1">{{ $trabajador->nombre_completo }}</h1>
          <p class="text-secondary mb-2">{{ $perfil?->oficio?->nombre }} · {{ $trabajador->distrito?->nombre }}, Cusco</p>
          <div class="d-flex flex-wrap align-items-center gap-2">
            @if ($trabajador->verificado)
              <span class="badge badge-verified rounded-pill"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Identidad verificada</span>
            @endif
            @if ($perfil)
              <span class="small {{ $perfil->disponible ? 'text-success' : 'text-secondary' }}">
                <i class="bi bi-circle-fill" style="font-size:.55rem" aria-hidden="true"></i>
                {{ $perfil->disponible ? 'Disponible para nuevos trabajos' : 'Ocupado ahora' }}
              </span>
            @endif
          </div>
        </div>
      </header>

      {{-- Resumen: nota, experiencia y tarifa --}}
      <div class="card-ayni p-3 mb-4">
        <div class="resumen">
          <div>
            @if ($total > 0)
              <div class="valor">{{ number_format($nota, 1) }} <i class="bi bi-star-fill stars" aria-hidden="true"></i></div>
              <small class="text-secondary">{{ $total }} {{ $total === 1 ? 'reseña' : 'reseñas' }}</small>
            @else
              <div class="valor">Nuevo</div><small class="text-secondary">sin reseñas aún</small>
            @endif
          </div>
          <div>
            <div class="valor">{{ $anios > 0 ? $anios : '—' }}</div>
            <small class="text-secondary">{{ $anios === 1 ? 'año de experiencia' : 'años de experiencia' }}</small>
          </div>
          <div><div class="valor">{{ $tarifa }}</div><small class="text-secondary">por visita</small></div>
        </div>
      </div>

      {{-- Gráfica: cuántas reseñas tiene cada puntaje --}}
      @if ($total > 0)
        <section class="card-ayni p-4 mb-4" aria-labelledby="titulo-distribucion">
          <h2 class="h5 mb-3" id="titulo-distribucion">Distribución de calificaciones</h2>
          <x-grafica-barras :datos="collect($distribucion)->mapWithKeys(fn ($n, $e) => [$e . ' ★' => $n])->all()"
                            titulo="Distribución de calificaciones" />
        </section>
      @endif

      <section class="card-ayni p-4 mb-4" id="sobre-mi">
        <h2 class="h5">Sobre mi trabajo</h2>
        @if ($perfil?->descripcion)
          {{-- e() escapa el texto y nl2br respeta los saltos de línea (seguro contra XSS) --}}
          <p class="mb-0">{!! nl2br(e($perfil->descripcion)) !!}</p>
        @else
          <p class="text-secondary mb-0">
            @if ($esMio)

            @if ($trabajador->cv)
  <a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('cv.descargar', $trabajador) }}" target="_blank" rel="noopener">
    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Ver currículum (CV)
  </a>
@endif
              Aún no escribes nada aquí. <a href="{{ route('perfil.edit') }}">Cuenta qué trabajos haces</a> para que más clientes confíen en ti.
            @else
              Este trabajador todavía no agregó una descripción.
            @endif
          </p>
        @endif
      </section>

      <section class="card-ayni p-4 mb-4" id="trabajos">
        <h2 class="h5 mb-3">Trabajos recientes</h2>
        <div class="galeria row g-2 row-cols-2 row-cols-md-4">
          @forelse ($fotos as $f)
            <div class="col">
              <a href="{{ asset('storage/' . $f) }}" target="_blank" rel="noopener">
                <img src="{{ asset('storage/' . $f) }}" alt="Trabajo de {{ $trabajador->name }}" loading="lazy">
              </a>
            </div>
          @empty
            @for ($i = 0; $i < 4; $i++)
              <div class="col"><div class="photo-slot"><i class="bi bi-image" aria-hidden="true"></i></div></div>
            @endfor
          @endforelse
        </div>
        @if ($fotos->isEmpty())
          <p class="small text-secondary mt-3 mb-0">
            @if ($esMio)
              Todavía no subiste fotos. <a href="{{ route('servicios.index') }}">Agrégalas en tus servicios</a>.
            @else
              Todavía no hay trabajos publicados.
            @endif
          </p>
        @endif
      </section>

      <section class="card-ayni p-4" id="resenas" aria-labelledby="titulo-resenas">
        <h2 class="h5 mb-3" id="titulo-resenas">Reseñas</h2>
        @forelse ($resenas as $r)
          <article class="d-flex gap-3 {{ $loop->last ? '' : 'pb-3 mb-3 border-bottom' }}">
            <x-avatar :user="$r->autor" size="sm" />
            <div>
              <div>
                <span class="stars" aria-label="{{ $r->puntuacion }} de 5 estrellas">{{ str_repeat('★', $r->puntuacion) . str_repeat('☆', 5 - $r->puntuacion) }}</span>
                <strong>{{ $r->autor->name }}</strong>
                <span class="text-secondary small">· {{ $r->autor->distrito?->nombre }}</span>
              </div>
              @if ($r->etiquetas)
                <div class="small text-secondary">{{ implode(' · ', $r->etiquetas) }}</div>
              @endif
              @if ($r->comentario)<p class="mb-0">{{ $r->comentario }}</p>@endif
            </div>
          </article>
        @empty
          <p class="text-secondary mb-0">Todavía no tiene reseñas.</p>
        @endforelse
        @if ($total > $resenas->count())
          <p class="small text-secondary mt-3 mb-0">Mostrando las {{ $resenas->count() }} reseñas más recientes de {{ $total }}.</p>
        @endif
      </section>
    </div>

    <aside class="col-lg-4">
      <div class="card-ayni p-4 sticky-side">
        <div class="h3 mb-0">{{ $tarifa }} <small class="fs-6 fw-normal text-secondary">por visita</small></div>
        <p class="text-secondary mt-2">Tarifa publicada por el trabajador.</p>

        @if ($esMio)
          {{-- Su propio perfil: accesos para mejorarlo en lugar de contactarse a sí mismo --}}
          <p class="note">Así te ven los clientes. Una foto clara y una buena descripción generan más confianza.</p>
          <a class="btn btn-ayni w-100 mb-2" href="{{ route('perfil.edit') }}"><i class="bi bi-camera" aria-hidden="true"></i> Editar mi perfil y foto</a>
          <a class="btn btn-outline-secondary w-100" href="{{ route('servicios.index') }}">Mis servicios</a>
        @else
          @if ($trabajador->whatsapp_url)
            <a class="btn btn-whatsapp w-100 py-2" target="_blank" rel="noopener"
               href="{{ $trabajador->whatsapp_url }}">
              <i class="bi bi-whatsapp" aria-hidden="true"></i> Contactar por WhatsApp
            </a>
          @else
            {{-- Sin teléfono válido no se muestra un enlace roto --}}
            <button type="button" class="btn btn-outline-secondary w-100 py-2" disabled>
              <i class="bi bi-whatsapp" aria-hidden="true"></i> Sin WhatsApp disponible
            </button>
          @endif
          @if ($trabajador->cv)
  <a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('cv.descargar', $trabajador) }}" target="_blank" rel="noopener">
    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Ver currículum (CV)
  </a>
@endif
          @if ($puedeCalificar)
            {{-- Si no hay sesión, el middleware lo manda al login y luego lo devuelve aquí --}}
            <a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('calificaciones.create', $trabajador) }}">
              <i class="bi bi-star" aria-hidden="true"></i> Calificar un trabajo
            </a>
            @guest
              <p class="small text-secondary mt-2 mb-0 text-center">Te pediremos ingresar para calificar.</p>
            @endguest
          @endif
          <p class="note mt-3 mb-0">Antes de contratar, revisa su identidad verificada y sus reseñas.</p>
        @endif
      </div>
    </aside>
  </div>
</main>
@endsection
