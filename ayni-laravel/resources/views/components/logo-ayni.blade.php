{{-- Logo grande de Ayni: chakana dorada dentro de aros giratorios (paleta andina).
     Uso: <x-logo-ayni />   Los aros y el brillo se animan con CSS (ver public/css/tema-andino.css). --}}
<svg {{ $attributes->merge(['viewBox' => '0 0 240 240', 'role' => 'img', 'aria-label' => 'Logo de Ayni: chakana andina']) }}>
  <defs>
    <linearGradient id="logo-oro" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#F8E3A0"/>
      <stop offset=".45" stop-color="#E4BC40"/>
      <stop offset="1" stop-color="#B8890F"/>
    </linearGradient>
  </defs>
  {{-- aro exterior de puntos que gira --}}
  <g class="aro-giro">
    <circle cx="120" cy="120" r="112" fill="none" stroke="#E4BC40" stroke-width="4" stroke-linecap="round" stroke-dasharray="1 13"/>
  </g>
  {{-- aro medio de rayas que gira al revés --}}
  <g class="aro-giro-inv">
    <circle cx="120" cy="120" r="101" fill="none" stroke="#F3F0E7" stroke-opacity=".55" stroke-width="2" stroke-dasharray="14 8"/>
  </g>
  <circle cx="120" cy="120" r="92" fill="#650000" fill-opacity=".55" stroke="#E4BC40" stroke-width="2"/>
  {{-- chakana (cruz andina escalonada) --}}
  <g class="chakana-logo">
    <g transform="translate(46 46) scale(6.2)">
      <path fill="url(#logo-oro)" stroke="#650000" stroke-width=".35" stroke-linejoin="round" fill-rule="evenodd"
            d="M9 0h6v3h3v3h3v3h3v6h-3v3h-3v3h-3v3H9v-3H6v-3H3v-3H0V9h3V6h3V3h3zM12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 1 0 0-7z"/>
      <circle cx="12" cy="12" r="1.7" fill="#9A0000"/>
      <circle cx="12" cy="12" r=".7" fill="#F3F0E7"/>
    </g>
  </g>
</svg>
