// =====================================================================
// Ayni · carruseles de la página principal (JS puro, no depende de Bootstrap)
//  1) [data-carrusel]        : diapositivas con fundido, puntos, flechas, autoavance y gesto de deslizar
//  2) [data-carrusel-cards]  : tarjetas desplazables con flechas (scroll-snap)
// =====================================================================
(() => {
  const reducido = matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.querySelectorAll('[data-carrusel]').forEach((car) => {
    const slides = [...car.querySelectorAll('.car-slide')];
    if (slides.length < 2) return;
    const ms = Number(car.dataset.ms) || 5500;
    const dots = car.querySelector('.car-dots');
    const barra = car.querySelector('.car-barra');
    let i = 0, timer = null, pausa = false;

    const botones = slides.map((_, k) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('aria-label', `Ir a la diapositiva ${k + 1}`);
      b.addEventListener('click', () => { ir(k); reiniciar(); });
      dots.appendChild(b);
      return b;
    });

    function ir(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach((s, k) => {
        s.classList.toggle('activa', k === i);
        s.setAttribute('aria-hidden', k === i ? 'false' : 'true');
        s.querySelectorAll('a,button').forEach((x) => (x.tabIndex = k === i ? 0 : -1));
      });
      botones.forEach((b, k) => b.setAttribute('aria-current', k === i ? 'true' : 'false'));
      if (barra) { barra.classList.remove('corre'); void barra.offsetWidth; if (!pausa && !reducido) barra.classList.add('corre'); }
    }
    function reiniciar() {
      clearInterval(timer);
      if (reducido) return;
      timer = setInterval(() => { if (!pausa) ir(i + 1); }, ms);
    }

    car.style.setProperty('--ms', ms + 'ms');
    car.querySelector('[data-prev]')?.addEventListener('click', () => { ir(i - 1); reiniciar(); });
    car.querySelector('[data-next]')?.addEventListener('click', () => { ir(i + 1); reiniciar(); });
    car.addEventListener('mouseenter', () => { pausa = true; barra?.classList.remove('corre'); });
    car.addEventListener('mouseleave', () => { pausa = false; ir(i); });
    car.addEventListener('focusin', () => { pausa = true; });
    car.addEventListener('focusout', () => { pausa = false; });
    car.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { ir(i - 1); reiniciar(); }
      if (e.key === 'ArrowRight') { ir(i + 1); reiniciar(); }
    });
    // Deslizar con el dedo
    let x0 = null;
    car.addEventListener('pointerdown', (e) => { x0 = e.clientX; });
    car.addEventListener('pointerup', (e) => {
      if (x0 === null) return;
      const dx = e.clientX - x0; x0 = null;
      if (Math.abs(dx) > 45) { ir(i + (dx < 0 ? 1 : -1)); reiniciar(); }
    });
    ir(0);
    reiniciar();
  });

  document.querySelectorAll('[data-carrusel-cards]').forEach((cont) => {
    const pista = cont.querySelector('.car-track');
    const paso = () => Math.max(pista.clientWidth * 0.8, 260);
    cont.querySelector('[data-prev]')?.addEventListener('click', () => pista.scrollBy({ left: -paso(), behavior: reducido ? 'auto' : 'smooth' }));
    cont.querySelector('[data-next]')?.addEventListener('click', () => {
      const fin = pista.scrollLeft + pista.clientWidth >= pista.scrollWidth - 4;
      if (fin) pista.scrollTo({ left: 0, behavior: reducido ? 'auto' : 'smooth' });
      else pista.scrollBy({ left: paso(), behavior: reducido ? 'auto' : 'smooth' });
    });
  });
})();
