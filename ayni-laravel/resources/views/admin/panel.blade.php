{{-- Resumen de métricas de impacto social: dashboard oscuro con gráficas (dona, pastel, barras, líneas, medidor).
     Las gráficas las dibuja public/js/graficas.js a partir de los atributos data-datos (JSON). --}}
@extends('layouts.app')
@section('titulo', 'Administración')

@section('contenido')
@php
  // Datos (vienen cacheados desde PanelController; los "?? []" evitan errores con un caché viejo)
  $estrellas = collect($metricas['distribucion'] ?? [])->mapWithKeys(fn ($n, $e) => [$e . ' ★' => $n])->all();
  $porOficio = $metricas['por_oficio'] ?? [];
  $porMes    = $metricas['por_mes'] ?? ['etiquetas' => [], 'calificaciones' => [], 'registros' => []];
  $ranking   = $metricas['ranking'] ?? [];
  $pctVerif  = $metricas['trabajadores'] > 0 ? round($metricas['verificados'] / $metricas['trabajadores'] * 100) : 0;

  $jsonVerif = json_encode([
      'titulo' => 'Verificación de trabajadores', 'centro' => 'Trabajadores',
      'etiquetas' => ['Verificados', 'Pendientes'],
      'valores' => [$metricas['verificados'], $metricas['pendientes']],
      'colores' => ['#E4BC40', '#C1121F'],
  ], JSON_UNESCAPED_UNICODE);
  $jsonEstrellas = json_encode([
      'titulo' => 'Calificaciones por estrella', 'vacio' => 'Aún no hay calificaciones.',
      'etiquetas' => array_keys($estrellas), 'valores' => array_values($estrellas),
      'colores' => ['#E4BC40', '#F2D77E', '#A8A39A', '#C1121F', '#8E1B1B'],
  ], JSON_UNESCAPED_UNICODE);
  $jsonOficios = json_encode([
      'titulo' => 'Trabajadores por oficio', 'vacio' => 'Aún no hay oficios con trabajadores.',
      'etiquetas' => array_keys($porOficio), 'valores' => array_values($porOficio),
      'colores' => ['#E4BC40', '#C1121F', '#A8A39A', '#F3F0E7', '#F2D77E', '#8E1B1B'],
  ], JSON_UNESCAPED_UNICODE);
  $jsonLinea = json_encode([
      'titulo' => 'Actividad de los últimos 6 meses', 'vacio' => 'Aún no hay actividad reciente.',
      'etiquetas' => $porMes['etiquetas'],
      'series' => [
          ['nombre' => 'Calificaciones', 'valores' => $porMes['calificaciones'], 'color' => '#E4BC40'],
          ['nombre' => 'Registros nuevos', 'valores' => $porMes['registros'], 'color' => '#F0646C'],
      ],
  ], JSON_UNESCAPED_UNICODE);
  $jsonMedidor = json_encode([
      'titulo' => 'Promedio general', 'valor' => $metricas['promedio'], 'max' => 5, 'texto' => 'de 5 estrellas', 'color' => '#E4BC40',
  ], JSON_UNESCAPED_UNICODE);
@endphp
<main class="container py-4 py-lg-5">
  <h1 class="display-title mb-3">Administración</h1>
  @include('admin._tabs')

  <div class="dash">
    {{-- Barra superior del dashboard --}}
    <div class="dash-barra">
      <h2>
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 0h6v3h3v3h3v3h3v6h-3v3h-3v3h-3v3H9v-3H6v-3H3v-3H0V9h3V6h3V3h3zM12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 1 0 0-7z"/></svg>
        <span>Panel de impacto social<small>Red Ayni · Cusco · {{ now()->translatedFormat('j \d\e F Y') }}</small></span>
      </h2>
      <div class="dash-botones">
        <span class="dash-vivo">Datos al día</span>
        <a class="dash-btn" href="{{ route('admin.calificaciones') }}">Moderar calificaciones</a>
        <a class="dash-btn" href="{{ route('admin.catalogos') }}">Catálogos</a>
        @if ($metricas['pendientes'] > 0)
          <a href="{{ route('admin.usuarios', ['estado' => 'pendientes']) }}" class="dash-btn rojo">
            Verificar {{ $metricas['pendientes'] }} trabajador(es) pendiente(s)
          </a>
        @endif
      </div>
    </div>

    <div class="dash-grid">
      {{-- Fila 1: indicadores --}}
      @foreach ([
        ['Trabajadores registrados', $metricas['trabajadores'], 'k-maiz', 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0zM4 21a8 8 0 0 1 16 0'],
        ['Identidades verificadas', $metricas['verificados'], 'k-alpaca', 'M12 3 4 6v6c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6zM8.5 12l2.5 2.5 4.5-5'],
        ['Pendientes de verificar', $metricas['pendientes'], 'k-rojo', 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0'],
        ['Clientes', $metricas['clientes'], 'k-piedra', 'M3 21v-2a5 5 0 0 1 5-5h4M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM17 14v6M14 17h6'],
        ['Servicios activos', $metricas['servicios'], 'k-maiz', 'M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z'],
        ['Calificaciones', $metricas['calificaciones'], 'k-rojo', 'M12 3l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 21l1.2-6.5L2.5 9.9l6.6-.9z'],
      ] as [$etiqueta, $valor, $clase, $icono])
        <div class="tile kpi dg-2 {{ $clase }}">
          <svg class="kpi-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icono }}"/></svg>
          <div class="kpi-et">{{ $etiqueta }}</div>
          <div class="kpi-val">{{ number_format($valor) }}</div>
          @if ($etiqueta === 'Identidades verificadas')
            <div class="mini-barra" aria-hidden="true"><span style="width: {{ $pctVerif }}%"></span></div>
            <div class="kpi-pie">{{ $pctVerif }}% de los trabajadores</div>
          @endif
        </div>
      @endforeach

      {{-- Fila 2: dona, pastel y medidor --}}
      <section class="tile dg-4" aria-labelledby="g-verif">
        <h3 id="g-verif">Verificación de trabajadores</h3>
        <p class="tile-sub">Identidades verificadas frente a pendientes</p>
        <div data-grafica="dona" data-datos='{{ $jsonVerif }}'></div>
        <noscript><x-grafica-barras :datos="['Verificados' => $metricas['verificados'], 'Pendientes' => $metricas['pendientes']]" titulo="Verificación de trabajadores" etiqueta="6.5rem" /></noscript>
      </section>

      <section class="tile dg-4" aria-labelledby="g-est">
        <h3 id="g-est">Calificaciones por estrella</h3>
        <p class="tile-sub">Cómo califican los clientes</p>
        @if (array_sum($estrellas) > 0)
          <div data-grafica="pastel" data-datos='{{ $jsonEstrellas }}'></div>
          <noscript><x-grafica-barras :datos="$estrellas" titulo="Calificaciones por estrella" /></noscript>
        @else
          <p class="graf-vacio">Aún no hay calificaciones.</p>
        @endif
      </section>

      <section class="tile dg-4" aria-labelledby="g-prom">
        <h3 id="g-prom">Promedio general</h3>
        <p class="tile-sub">Satisfacción de toda la plataforma</p>
        <div data-grafica="medidor" data-datos='{{ $jsonMedidor }}'></div>
        <div class="mini-barra mt-3" aria-hidden="true"><span style="width: {{ $metricas['promedio'] / 5 * 100 }}%"></span></div>
        <div class="kpi-pie mt-2" style="font-size:.8rem;color:#A8A39A">{{ $metricas['calificaciones'] }} calificaciones en total</div>
      </section>

      {{-- Fila 3: líneas y barras --}}
      <section class="tile dg-7" aria-labelledby="g-act">
        <h3 id="g-act">Actividad de los últimos 6 meses</h3>
        <p class="tile-sub">Calificaciones recibidas y registros nuevos</p>
        <div data-grafica="linea" data-datos='{{ $jsonLinea }}'></div>
      </section>

      <section class="tile dg-5" aria-labelledby="g-ofi">
        <h3 id="g-ofi">Trabajadores por oficio</h3>
        <p class="tile-sub">Dónde está la oferta de la red</p>
        @if ($porOficio)
          <div data-grafica="barras" data-datos='{{ $jsonOficios }}'></div>
          <noscript><x-grafica-barras :datos="$porOficio" titulo="Trabajadores por oficio" :plomo="true" etiqueta="7rem" /></noscript>
        @else
          <p class="graf-vacio">Aún no hay oficios con trabajadores.</p>
        @endif
      </section>

      {{-- Fila 4: ranking (como "Agent Performance" del ejemplo) --}}
      <section class="tile dg-12" aria-labelledby="g-rank">
        <h3 id="g-rank">Mejores trabajadores</h3>
        <p class="tile-sub">Top 5 por calificación promedio</p>
        <div class="table-responsive">
          <table class="rank">
            <thead><tr><th>#</th><th>Trabajador</th><th>Oficio</th><th>Distrito</th><th>Identidad</th><th>Calificación</th><th>Reseñas</th></tr></thead>
            <tbody>
              @forelse ($ranking as $f)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <a class="quien text-decoration-none" href="{{ route('trabajadores.show', $f['id']) }}" style="color:inherit">
                      <span class="avatar avatar-c{{ $f['color'] }}"><span aria-hidden="true">{{ $f['iniciales'] }}</span></span>
                      {{ $f['nombre'] }}
                    </a>
                  </td>
                  <td>{{ $f['oficio'] ?? '—' }}</td>
                  <td>{{ $f['distrito'] ?? '—' }}</td>
                  <td><span class="pill {{ $f['verificado'] ? 'ok' : 'pend' }}">{{ $f['verificado'] ? 'Verificado' : 'Pendiente' }}</span></td>
                  <td>
                    @if ($f['resenas'] > 0)
                      <div class="nota-barra"><strong>{{ number_format($f['nota'], 1) }}</strong><div class="mini-barra" aria-hidden="true"><span style="width: {{ $f['nota'] / 5 * 100 }}%"></span></div></div>
                    @else
                      <span style="color:#A8A39A">Nuevo</span>
                    @endif
                  </td>
                  <td>{{ $f['resenas'] }}</td>
                </tr>
              @empty
                <tr class="rank-vacio"><td colspan="7">Aún no hay trabajadores con perfil.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</main>
@endsection

@push('scripts')
  <script src="{{ asset('js/graficas.js') }}"></script>
@endpush
