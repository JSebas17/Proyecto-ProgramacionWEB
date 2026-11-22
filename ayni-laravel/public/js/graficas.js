// =====================================================================
// Ayni · gráficas del panel de administración (SVG puro, sin librerías ni CDN)
// Uso:  <div data-grafica="dona|pastel|barras|linea|medidor" data-datos='{...JSON...}'></div>
// Todos los textos se insertan con textContent (nada de HTML crudo).
// =====================================================================
(() => {
  const NS = 'http://www.w3.org/2000/svg';
  const PALETA = ['#E4BC40', '#C1121F', '#A8A39A', '#F3F0E7', '#8E1B1B', '#F2D77E']; // maíz, rojo, piedra, alpaca y tonos
  const fmt = (n) => new Intl.NumberFormat('es-PE', { maximumFractionDigits: 1 }).format(n);
  const color = (d, i) => (d.colores && d.colores[i]) || PALETA[i % PALETA.length];
  const reducido = matchMedia('(prefers-reduced-motion: reduce)').matches;

  const svg = (tag, attrs = {}, padre) => {
    const e = document.createElementNS(NS, tag);
    for (const k in attrs) e.setAttribute(k, attrs[k]);
    if (padre) padre.appendChild(e);
    return e;
  };
  const html = (tag, clase, texto, padre) => {
    const e = document.createElement(tag);
    if (clase) e.className = clase;
    if (texto !== undefined) e.textContent = texto;
    if (padre) padre.appendChild(e);
    return e;
  };

  // ---- Tooltip único para todas las gráficas ----
  const tip = html('div', 'graf-tip', undefined, document.body);
  tip.hidden = true;
  tip.setAttribute('role', 'status');
  const mostrar = (ev, titulo, valor, c) => {
    tip.replaceChildren();
    if (c) { const sw = html('i', '', undefined, tip); sw.style.background = c; }
    html('span', '', titulo + ': ', tip);
    html('b', '', valor, tip);
    tip.hidden = false;
    const x = Math.min(ev.clientX + 14, innerWidth - tip.offsetWidth - 8);
    tip.style.left = Math.max(8, x) + 'px';
    tip.style.top = ev.clientY + 16 + 'px';
  };
  const ocultar = () => { tip.hidden = true; };
  const enlazar = (nodo, titulo, valor, c) => {
    nodo.addEventListener('mousemove', (e) => mostrar(e, titulo, valor, c));
    nodo.addEventListener('mouseleave', ocultar);
    nodo.addEventListener('focus', () => { const r = nodo.getBoundingClientRect(); mostrar({ clientX: r.left + r.width / 2, clientY: r.top }, titulo, valor, c); });
    nodo.addEventListener('blur', ocultar);
  };
  const siguiente = (fn) => (reducido ? fn() : requestAnimationFrame(() => requestAnimationFrame(fn)));
  const resumen = (d) => `${d.titulo || 'Gráfica'}. ` + (d.etiquetas || []).map((e, i) => `${e}: ${d.valores ? d.valores[i] : ''}`).join(', ');

  // ---- Utilidades de escala ----
  // Máximo del eje con 4 divisiones de números enteros (p. ej. 12 -> 3, 6, 9, 12)
  const maxBonito = (m) => {
    const crudo = Math.max(m, 1) / 4;
    const paso = crudo <= 10 ? Math.ceil(crudo) : crudo <= 20 ? Math.ceil(crudo / 2) * 2 : crudo <= 50 ? Math.ceil(crudo / 5) * 5 : Math.ceil(crudo / 10) * 10;
    return paso * 4;
  };

  // ---- Leyenda HTML ----
  function leyenda(d, total, padre, nodosFilas) {
    const ul = html('ul', 'graf-leyenda', undefined, padre);
    d.etiquetas.forEach((et, i) => {
      const li = html('li', '', undefined, ul);
      li.style.setProperty('--c', color(d, i));
      html('i', '', undefined, li);
      html('span', '', et, li);
      html('b', '', fmt(d.valores[i]), li);
      html('span', 'pct', total ? Math.round((d.valores[i] / total) * 100) + '%' : '0%', li);
      if (nodosFilas && nodosFilas[i]) {
        li.addEventListener('mouseenter', () => nodosFilas[i].dispatchEvent(new Event('resaltar')));
      }
    });
  }

  // ---- Dona ----
  function dona(caja, d) {
    const total = d.valores.reduce((a, b) => a + b, 0);
    const cont = html('div', 'graf-donut', undefined, caja);
    const s = svg('svg', { viewBox: '0 0 200 200', class: 'graf-svg', role: 'img', 'aria-label': resumen(d) }, cont);
    const R = 76, W = 28, C = 2 * Math.PI * R;
    svg('circle', { cx: 100, cy: 100, r: R, fill: 'none', stroke: 'rgba(243,240,231,.08)', 'stroke-width': W }, s);
    const activos = d.valores.filter((v) => v > 0).length;
    let acum = 0;
    const segs = [];
    d.valores.forEach((v, i) => {
      if (v <= 0) { segs.push(null); return; }
      const largo = (v / total) * C;
      const hueco = activos > 1 ? 3 : 0;
      const c = svg('circle', {
        cx: 100, cy: 100, r: R, fill: 'none', stroke: color(d, i), 'stroke-width': W,
        'stroke-dashoffset': -acum, transform: 'rotate(-90 100 100)', class: 'graf-seg', tabindex: 0,
      }, s);
      c.style.strokeDasharray = `0 ${C}`;
      siguiente(() => { c.style.strokeDasharray = `${Math.max(largo - hueco, 0.5)} ${C}`; });
      enlazar(c, d.etiquetas[i], `${fmt(v)} (${Math.round((v / total) * 100)}%)`, color(d, i));
      segs.push(c);
      acum += largo;
    });
    svg('text', { x: 100, y: 104, 'text-anchor': 'middle', class: 'graf-centro-num' }, s).textContent = fmt(total);
    svg('text', { x: 100, y: 124, 'text-anchor': 'middle', class: 'graf-centro-txt' }, s).textContent = d.centro || 'Total';
    leyenda(d, total, cont, segs);
  }

  // ---- Pastel ----
  function pastel(caja, d) {
    const total = d.valores.reduce((a, b) => a + b, 0);
    const cont = html('div', 'graf-donut', undefined, caja);
    const s = svg('svg', { viewBox: '0 0 200 200', class: 'graf-svg', role: 'img', 'aria-label': resumen(d) }, cont);
    const cx = 100, cy = 100, r = 92;
    const punto = (ang, rad) => [cx + rad * Math.cos(ang), cy + rad * Math.sin(ang)];
    let ang = -Math.PI / 2;
    const nodos = [];
    const activos = d.valores.filter((v) => v > 0).length;
    d.valores.forEach((v, i) => {
      if (v <= 0) { nodos.push(null); return; }
      const frac = v / total, fin = ang + frac * 2 * Math.PI;
      let g;
      if (activos === 1) {
        g = svg('circle', { cx, cy, r, fill: color(d, i), class: 'graf-rebanada', tabindex: 0 }, s);
      } else {
        const [x1, y1] = punto(ang, r), [x2, y2] = punto(fin, r);
        g = svg('path', {
          d: `M${cx} ${cy} L${x1.toFixed(2)} ${y1.toFixed(2)} A${r} ${r} 0 ${frac > 0.5 ? 1 : 0} 1 ${x2.toFixed(2)} ${y2.toFixed(2)} Z`,
          fill: color(d, i), stroke: '#34312D', 'stroke-width': 2.5, 'stroke-linejoin': 'round', class: 'graf-rebanada', tabindex: 0,
        }, s);
      }
      g.style.transformOrigin = `${cx}px ${cy}px`;
      if (!reducido) { g.style.opacity = 0; g.style.transform = 'scale(.6)'; siguiente(() => { g.style.transition = `opacity .6s ease ${i * 0.08}s, transform .7s cubic-bezier(.2,.7,.2,1) ${i * 0.08}s`; g.style.opacity = ''; g.style.transform = ''; }); }
      enlazar(g, d.etiquetas[i], `${fmt(v)} (${Math.round(frac * 100)}%)`, color(d, i));
      nodos.push(g);
      if (frac >= 0.07) {
        const [tx, ty] = punto(ang + (fin - ang) / 2, r * 0.66);
        const claro = ['#E4BC40', '#F3F0E7', '#F2D77E', '#A8A39A'].includes(color(d, i));
        const t = svg('text', { x: tx, y: ty + 4, 'text-anchor': 'middle', 'pointer-events': 'none', style: `font:800 13px var(--fuente-titulos);fill:${claro ? '#2B2927' : '#fff'}` }, s);
        t.textContent = Math.round(frac * 100) + '%';
      }
      ang = fin;
    });
    leyenda(d, total, cont, nodos);
  }

  // ---- Barras verticales ----
  function barras(caja, d) {
    const W = 560, H = 270, ml = 34, mr = 8, mt = 22, mb = 44;
    const s = svg('svg', { viewBox: `0 0 ${W} ${H}`, class: 'graf-svg', role: 'img', 'aria-label': resumen(d) }, caja);
    const max = maxBonito(Math.max(...d.valores, 1));
    const alto = H - mt - mb, banda = (W - ml - mr) / d.valores.length;
    const y = (v) => mt + alto - (v / max) * alto;
    for (let t = 0; t <= 4; t++) {
      const v = (max / 4) * t, yy = y(v);
      svg('line', { x1: ml, x2: W - mr, y1: yy, y2: yy, class: t === 0 ? 'graf-eje' : 'graf-grid' }, s);
      svg('text', { x: ml - 7, y: yy + 4, 'text-anchor': 'end', class: 'graf-txt' }, s).textContent = fmt(v);
    }
    d.valores.forEach((v, i) => {
      const bw = Math.min(banda * 0.58, 56), x = ml + banda * i + (banda - bw) / 2, h = (v / max) * alto;
      const r = svg('rect', { x, y: mt + alto - h, width: bw, height: Math.max(h, 0), rx: 6, fill: color(d, i), class: 'graf-barra', tabindex: 0 }, s);
      r.style.transformBox = 'fill-box'; r.style.transformOrigin = '50% 100%';
      if (!reducido) { r.style.transform = 'scaleY(0)'; siguiente(() => { r.style.transitionDelay = i * 0.07 + 's'; r.style.transform = 'scaleY(1)'; }); }
      enlazar(r, d.etiquetas[i], fmt(v), color(d, i));
      svg('text', { x: x + bw / 2, y: mt + alto - h - 7, 'text-anchor': 'middle', class: 'graf-val' }, s).textContent = fmt(v);
      const et = d.etiquetas[i].length > 11 ? d.etiquetas[i].slice(0, 10) + '…' : d.etiquetas[i];
      svg('text', { x: x + bw / 2, y: H - mb + 20, 'text-anchor': 'middle', class: 'graf-txt' }, s).textContent = et;
    });
  }

  // ---- Líneas suaves con área ----
  function linea(caja, d) {
    const W = 600, H = 270, ml = 34, mr = 14, mt = 18, mb = 38;
    const s = svg('svg', { viewBox: `0 0 ${W} ${H}`, class: 'graf-svg', role: 'img', 'aria-label': d.titulo + '. ' + d.series.map((se) => se.nombre + ': ' + se.valores.join(', ')).join('. ') }, caja);
    const defs = svg('defs', {}, s);
    const max = maxBonito(Math.max(1, ...d.series.flatMap((se) => se.valores)));
    const alto = H - mt - mb, n = d.etiquetas.length, paso = n > 1 ? (W - ml - mr) / (n - 1) : 0;
    const X = (i) => ml + paso * i, Y = (v) => mt + alto - (v / max) * alto;
    for (let t = 0; t <= 4; t++) {
      const v = (max / 4) * t, yy = Y(v);
      svg('line', { x1: ml, x2: W - mr, y1: yy, y2: yy, class: t === 0 ? 'graf-eje' : 'graf-grid' }, s);
      svg('text', { x: ml - 7, y: yy + 4, 'text-anchor': 'end', class: 'graf-txt' }, s).textContent = fmt(v);
    }
    d.etiquetas.forEach((et, i) => svg('text', { x: X(i), y: H - 12, 'text-anchor': 'middle', class: 'graf-txt' }, s).textContent = et);
    const suave = (pts) => {
      let p = `M${pts[0][0]} ${pts[0][1]}`;
      for (let i = 0; i < pts.length - 1; i++) {
        const a = pts[i - 1] || pts[i], b = pts[i], c = pts[i + 1], e = pts[i + 2] || c, k = 0.18;
        p += ` C${(b[0] + (c[0] - a[0]) * k).toFixed(1)} ${(b[1] + (c[1] - a[1]) * k).toFixed(1)}, ${(c[0] - (e[0] - b[0]) * k).toFixed(1)} ${(c[1] - (e[1] - b[1]) * k).toFixed(1)}, ${c[0].toFixed(1)} ${c[1].toFixed(1)}`;
      }
      return p;
    };
    const id = 'g' + Math.random().toString(36).slice(2, 8);
    d.series.forEach((se, si) => {
      const c = se.color || color(d, si);
      const grad = svg('linearGradient', { id: `${id}-${si}`, x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
      svg('stop', { offset: '0%', 'stop-color': c, 'stop-opacity': 0.4 }, grad);
      svg('stop', { offset: '100%', 'stop-color': c, 'stop-opacity': 0 }, grad);
      const pts = se.valores.map((v, i) => [X(i), Y(v)]);
      if (pts.length > 1) {
        const trazo = suave(pts);
        svg('path', { d: `${trazo} L${X(n - 1)} ${mt + alto} L${X(0)} ${mt + alto} Z`, fill: `url(#${id}-${si})`, class: 'graf-area' }, s);
        const l = svg('path', { d: trazo, stroke: c, class: 'graf-linea' }, s);
        if (!reducido) {
          const L = l.getTotalLength();
          l.style.strokeDasharray = L; l.style.strokeDashoffset = L;
          siguiente(() => { l.style.transition = 'stroke-dashoffset 1.4s ease'; l.style.strokeDashoffset = 0; });
        }
      }
      pts.forEach((p, i) => {
        const pt = svg('circle', { cx: p[0], cy: p[1], r: 5, fill: c, class: 'graf-punto', tabindex: 0 }, s);
        pt.style.transformBox = 'fill-box'; pt.style.transformOrigin = 'center';
        enlazar(pt, `${se.nombre} · ${d.etiquetas[i]}`, fmt(se.valores[i]), c);
      });
    });
    siguiente(() => caja.classList.add('graf-lista'));
    const lg = html('div', 'graf-leyenda-linea', undefined, caja);
    d.series.forEach((se, si) => { const sp = html('span', '', undefined, lg); sp.style.setProperty('--c', se.color || color(d, si)); html('i', '', undefined, sp); sp.append(se.nombre); });
  }

  // ---- Medidor semicircular ----
  function medidor(caja, d) {
    const s = svg('svg', { viewBox: '0 0 200 128', class: 'graf-svg', role: 'img', 'aria-label': `${d.titulo}: ${d.valor} de ${d.max}` }, caja);
    const L = Math.PI * 80;
    const arco = 'M20 104 A80 80 0 0 1 180 104';
    svg('path', { d: arco, fill: 'none', stroke: 'rgba(243,240,231,.1)', 'stroke-width': 18, 'stroke-linecap': 'round' }, s);
    const p = svg('path', { d: arco, fill: 'none', stroke: d.color || PALETA[0], 'stroke-width': 18, 'stroke-linecap': 'round', class: 'graf-seg' }, s);
    p.style.strokeDasharray = `0 ${L}`;
    siguiente(() => { p.style.transition = 'stroke-dasharray 1.3s cubic-bezier(.2,.7,.2,1)'; p.style.strokeDasharray = `${(Math.min(d.valor, d.max) / d.max) * L} ${L}`; });
    svg('text', { x: 100, y: 90, 'text-anchor': 'middle', class: 'graf-gauge-num' }, s).textContent = d.valor ? fmt(d.valor) : '—';
    svg('text', { x: 100, y: 112, 'text-anchor': 'middle', class: 'graf-centro-txt' }, s).textContent = d.texto || `de ${d.max}`;
    svg('text', { x: 20, y: 124, 'text-anchor': 'middle', class: 'graf-txt' }, s).textContent = '0';
    svg('text', { x: 180, y: 124, 'text-anchor': 'middle', class: 'graf-txt' }, s).textContent = fmt(d.max);
    enlazar(p, d.titulo, `${fmt(d.valor)} / ${fmt(d.max)}`, d.color || PALETA[0]);
  }

  const TIPOS = { dona, pastel, barras, linea, medidor };
  document.querySelectorAll('[data-grafica]').forEach((caja) => {
    try {
      const d = JSON.parse(caja.dataset.datos || '{}');
      const total = (d.valores || []).reduce((a, b) => a + b, 0);
      caja.classList.add('graf');
      const vacio = d.series ? d.series.every((se) => !se.valores.some(Boolean)) : (d.valores ? total === 0 : false);
      if (vacio && caja.dataset.grafica !== 'dona') { html('p', 'graf-vacio', d.vacio || 'Aún no hay datos para mostrar.', caja); return; }
      TIPOS[caja.dataset.grafica](caja, d);
    } catch (e) {
      console.error('Gráfica no disponible', e);
    }
  });
})();
