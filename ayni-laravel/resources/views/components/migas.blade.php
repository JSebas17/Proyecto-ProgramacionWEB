{{-- Migas de pan: dónde estás y cómo volver.
     Uso: <x-migas :items="[['Inicio', route('home')], ['Perfil', $url], ['Calificar']]" />
     El último elemento (sin enlace) es la página actual. --}}
@props(['items'])
<nav aria-label="Ruta de navegación" class="migas">
  <ol>
    @foreach ($items as $item)
      <li @if ($loop->last) aria-current="page" @endif>
        @if (! $loop->last && isset($item[1]))<a href="{{ $item[1] }}">{{ $item[0] }}</a>@else{{ $item[0] }}@endif
      </li>
    @endforeach
  </ol>
</nav>
