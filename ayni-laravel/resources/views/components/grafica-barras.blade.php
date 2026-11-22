{{-- Gráfica de barras horizontales en blanco, negro y plomo.
     Uso: <x-grafica-barras :datos="['5 ★' => 12, '4 ★' => 3]" titulo="Calificaciones" />
     El ancho de cada barra es proporcional al valor más alto. Es accesible: el resumen va en aria-label. --}}
@props(['datos', 'titulo' => '', 'plomo' => false, 'etiqueta' => '4.5rem'])
@php
  $datos = (array) $datos;
  $max = max(1, ...array_values($datos ?: [0]));
  $resumen = collect($datos)->map(fn ($v, $k) => "$k: $v")->implode(', ');
@endphp
<div class="grafica" role="img" aria-label="{{ $titulo }}. {{ $resumen }}" style="--etq: {{ $etiqueta }}">
  @foreach ($datos as $nombre => $valor)
    <div class="grafica-fila" aria-hidden="true">
      <span class="etq">{{ $nombre }}</span>
      <div class="grafica-pista"><div class="grafica-barra {{ $plomo ? 'plomo' : '' }}" style="width: {{ round($valor / $max * 100) }}%"></div></div>
      <span class="grafica-valor">{{ $valor }}</span>
    </div>
  @endforeach
</div>
