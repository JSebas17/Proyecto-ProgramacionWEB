{{-- Perfil público del trabajador (reemplaza a perfil.php). --}}
@extends('layouts.app')
@section('titulo', $trabajador->nombre_completo)

@section('contenido')
@php
  $perfil = $trabajador->perfilTrabajador;
  $nota   = $trabajador->calificaciones_recibidas_avg_puntuacion;
  $total  = $trabajador->calificaciones_recibidas_count;
  $tarifa = $perfil?->tarifa_referencial ? 'S/ ' . number_format($perfil->tarifa_referencial, 0) : 'A convenir';
  // Saludo precodificado para el enlace de WhatsApp
  $saludo = rawurlencode('Hola ' . $trabajador->name . ', te vi en Ayni');
@endphp
<main class="container py-4 py-lg-5">
  <a href="{{ route('home') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a resultados</a>

  <div class="row g-4 mt-1">
    <div class="col-lg-8">
      <header class="d-flex align-items-center gap-3 mb-4">
        <div class="avatar avatar-lg">{{ $trabajador->iniciales }}</div>
        <div>
          <h1 class="h2 mb-1">{{ $trabajador->nombre_completo }}</h1>
          <p class="text-secondary mb-2">{{ $perfil?->oficio?->nombre }} · {{ $trabajador->distrito?->nombre }}, Cusco</p>
          @if ($trabajador->verificado)
            <span class="badge badge-verified rounded-pill"><i class="bi bi-patch-check-fill"></i> Identidad verificada</span>
          @endif
        </div>
      </header>

      {{-- Resumen: nota y tarifa --}}
      <div class="card-ayni shadow-sm p-3 mb-4">
        <div class="row text-center g-2">
          <div class="col-6">
            @if ($total > 0)
              <div class="h4 mb-0">{{ number_format($nota, 1) }} <i class="bi bi-star-fill stars"></i></div>
              <small class="text-secondary">{{ $total }} reseñas</small>
            @else
              <div class="h4 mb-0">Nuevo</div><small class="text-secondary">sin reseñas aún</small>
            @endif
          </div>
          <div class="col-6"><div class="h4 mb-0">{{ $tarifa }}</div><small class="text-secondary">por visita</small></div>
        </div>
      </div>

      @if ($perfil?->descripcion)
        <section class="card-ayni shadow-sm p-4 mb-4">
          <h2 class="h5">Sobre mi trabajo</h2>
          {{-- e() escapa el texto y nl2br respeta los saltos de línea (seguro contra XSS) --}}
          <p class="mb-0">{!! nl2br(e($perfil->descripcion)) !!}</p>
        </section>
      @endif

      <section class="card-ayni shadow-sm p-4 mb-4">
        <h2 class="h5 mb-3">Trabajos recientes</h2>
        <div class="row g-2 row-cols-2 row-cols-md-4">
          @forelse ($fotos as $f)
            <div class="col"><img src="{{ asset('storage/' . $f) }}" alt="Trabajo de {{ $trabajador->name }}" class="img-fluid rounded"></div>
          @empty
            @for ($i = 0; $i < 4; $i++)
              <div class="col"><div class="photo-slot"><i class="bi bi-image"></i></div></div>
            @endfor
          @endforelse
        </div>
      </section>

      <section class="card-ayni shadow-sm p-4">
        <h2 class="h5 mb-3">Reseñas</h2>
        @forelse ($resenas as $r)
          <article class="pb-3 mb-3 border-bottom">
            <div>
              <span class="stars">{{ str_repeat('★', $r->puntuacion) . str_repeat('☆', 5 - $r->puntuacion) }}</span>
              <strong>{{ $r->autor->name }}</strong>
              <span class="text-secondary small">· {{ $r->autor->distrito?->nombre }}</span>
            </div>
            @if ($r->etiquetas)
              <div class="small text-secondary">{{ implode(' · ', $r->etiquetas) }}</div>
            @endif
            @if ($r->comentario)<p class="mb-0">{{ $r->comentario }}</p>@endif
          </article>
        @empty
          <p class="text-secondary mb-0">Todavía no tiene reseñas.</p>
        @endforelse
      </section>
    </div>

    <aside class="col-lg-4">
      <div class="card-ayni shadow-sm p-4 sticky-side">
        <div class="h3 mb-0">{{ $tarifa }} <small class="fs-6 fw-normal text-secondary">por visita</small></div>
        <p class="text-secondary mt-2">Tarifa publicada por el trabajador.</p>
        <a class="btn btn-whatsapp w-100 py-2 fw-semibold" target="_blank" rel="noopener"
           href="https://wa.me/{{ $trabajador->whatsapp }}?text={{ $saludo }}">
          <i class="bi bi-whatsapp"></i> Contactar por WhatsApp
        </a>
        {{-- Calificar: visible para cualquiera; si no hay sesión, el middleware lo manda al login --}}
        @if (auth()->id() !== $trabajador->id)
          <a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('calificaciones.create', $trabajador) }}">
            <i class="bi bi-star"></i> Calificar un trabajo
          </a>
        @endif
        <p class="note mt-3 mb-0">Antes de contratar, revisa su identidad verificada y sus reseñas.</p>
      </div>
    </aside>
  </div>
</main>
@endsection