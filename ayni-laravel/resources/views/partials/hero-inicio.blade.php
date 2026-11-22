{{-- Héroe de la página principal: logo grande con destellos + carrusel de mensajes.
     Se incluye desde home.blade.php. Los íconos son SVG propios (no dependen de ninguna librería). --}}
<section class="hero-ayni" aria-label="Bienvenida a Ayni">
  {{-- Destellos: estrellitas que titilan (decorativas) --}}
  <div class="destellos" aria-hidden="true">
    <span class="destello" style="--x:6%;  --y:14%; --s:22px; --d:.1s; --t:3.4s"></span>
    <span class="destello" style="--x:30%; --y:8%;  --s:14px; --d:1.2s; --t:2.8s; --c:#F3F0E7"></span>
    <span class="destello" style="--x:44%; --y:22%; --s:26px; --d:.6s; --t:3.8s"></span>
    <span class="destello" style="--x:12%; --y:74%; --s:16px; --d:1.8s; --t:3s;   --c:#F3F0E7"></span>
    <span class="destello" style="--x:36%; --y:84%; --s:24px; --d:.3s; --t:3.6s"></span>
    <span class="destello" style="--x:48%; --y:60%; --s:12px; --d:2.2s; --t:2.6s; --c:#F3F0E7"></span>
    <span class="destello" style="--x:94%; --y:12%; --s:20px; --d:.9s; --t:3.2s"></span>
    <span class="destello" style="--x:90%; --y:86%; --s:26px; --d:1.5s; --t:3.9s"></span>
    <span class="destello" style="--x:3%;  --y:46%; --s:12px; --d:2.6s; --t:2.9s; --c:#F3F0E7"></span>
    <span class="destello" style="--x:55%; --y:6%;  --s:16px; --d:2s;   --t:3.3s"></span>
  </div>

  <div class="container">
    <div class="hero-grid">
      {{-- Columna izquierda: LOGO GRANDE --}}
      <div>
        <div class="logo-grande">
          <div class="logo-emblema"><x-logo-ayni /></div>
          <div>
            <p class="logo-palabra">Ayni</p>
            <span class="logo-lema">Red de empleabilidad para <b>trabajadores independientes</b> · Cusco</span>
          </div>
        </div>

        <p class="hero-texto">
          Ayni es la ayuda mutua andina: hoy por ti, mañana por mí. Encuentra electricistas, gasfiteros, pintores y más,
          con identidad verificada y reseñas reales.
        </p>

        <div class="hero-acciones">
          <a class="btn-hero btn-brillo" href="#buscar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            Buscar trabajo
          </a>
          <a class="btn-hero btn-hero-2" href="{{ route('ofrecer') }}">Ofrecer mi servicio</a>
        </div>

        <ul class="hero-datos">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>Identidad verificada</li>
          <li><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.5 2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>Calificación mutua</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 11.5a8 8 0 0 1-11.7 7L4 20l1.5-4.2A8 8 0 1 1 20 11.5z"/></svg>Contacto directo por WhatsApp</li>
        </ul>
      </div>

      {{-- Columna derecha: CARRUSEL (public/js/inicio.js) --}}
      <div class="carrusel" data-carrusel data-ms="5500" role="region" aria-roledescription="carrusel" aria-label="Por qué elegir Ayni" tabindex="0">
        <article class="car-slide car-1 activa">
          <svg class="car-marca" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 0h6v3h3v3h3v3h3v6h-3v3h-3v3h-3v3H9v-3H6v-3H3v-3H0V9h3V6h3V3h3zM12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 1 0 0-7z"/></svg>
          <svg class="car-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>
          <h2>Contrata con confianza</h2>
          <p>Cada trabajador pasa por una verificación de identidad. Busca la insignia dorada en su perfil.</p>
          <a class="car-enlace" href="#buscar">Ver verificados →</a>
        </article>
        <article class="car-slide car-2" aria-hidden="true">
          <svg class="car-marca" viewBox="0 0 24 24" fill="currentColor" style="color:#9A0000" aria-hidden="true"><path fill-rule="evenodd" d="M9 0h6v3h3v3h3v3h3v6h-3v3h-3v3h-3v3H9v-3H6v-3H3v-3H0V9h3V6h3V3h3zM12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 1 0 0-7z"/></svg>
          <svg class="car-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 11V7a2 2 0 0 1 4 0v4M11 11V5a2 2 0 0 1 4 0v6M15 11V8a2 2 0 0 1 4 0v6a8 8 0 0 1-8 8H9a6 6 0 0 1-5-3l-2-3a2 2 0 0 1 3-2l2 2V9"/></svg>
          <h2>Ayni: hoy por ti, mañana por mí</h2>
          <p>Una red de ayuda mutua que acerca a las familias de Cusco con quienes saben hacer bien su oficio.</p>
          <a class="car-enlace" href="{{ route('ofrecer') }}">Unirme a la red →</a>
        </article>
        <article class="car-slide car-3" aria-hidden="true">
          <svg class="car-marca" viewBox="0 0 24 24" fill="currentColor" style="color:#E4BC40" aria-hidden="true"><path fill-rule="evenodd" d="M9 0h6v3h3v3h3v3h3v6h-3v3h-3v3h-3v3H9v-3H6v-3H3v-3H0V9h3V6h3V3h3zM12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 1 0 0-7z"/></svg>
          <svg class="car-icono" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.5 2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>
          <h2>Califica y sé calificado</h2>
          <p>Las reseñas son públicas y de servicios reales. Así la buena reputación se gana trabajando.</p>
          <a class="car-enlace" href="#destacados">Ver mejor calificados →</a>
        </article>
        <article class="car-slide car-4" aria-hidden="true">
          <svg class="car-marca" viewBox="0 0 24 24" fill="currentColor" style="color:#E4BC40" aria-hidden="true"><path fill-rule="evenodd" d="M9 0h6v3h3v3h3v3h3v6h-3v3h-3v3h-3v3H9v-3H6v-3H3v-3H0V9h3V6h3V3h3zM12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 1 0 0-7z"/></svg>
          <svg class="car-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/></svg>
          <h3>Oficios de Cusco a un clic</h3>
          <p>Electricistas, gasfiteros, pintores, carpinteros, limpieza y hotelería en tu distrito.</p>
          <a class="car-enlace" href="#buscar">Explorar oficios →</a>
        </article>

        <button type="button" class="car-ctrl car-prev" data-prev aria-label="Diapositiva anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button>
        <button type="button" class="car-ctrl car-next" data-next aria-label="Diapositiva siguiente"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
        <div class="car-dots" role="group" aria-label="Elegir diapositiva"></div>
        <div class="car-barra" aria-hidden="true"></div>
      </div>
    </div>
  </div>
  <div class="tejido" role="presentation"></div>
</section>
