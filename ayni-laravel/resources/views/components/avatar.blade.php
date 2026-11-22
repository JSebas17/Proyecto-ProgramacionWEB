{{-- Avatar: muestra la foto de perfil si existe; si no, las iniciales sobre un color estable.
     Uso: <x-avatar :user="$usuario" size="lg" alt="Foto de ..." />
     size: sm | (vacío = mediano) | lg | xl.  alt vacío = imagen decorativa (el nombre ya está al lado). --}}
@props(['user', 'size' => '', 'alt' => ''])
@php
  $clases = trim('avatar avatar-c' . $user->color_avatar . ($size ? ' avatar-' . $size : ''));
@endphp
<span {{ $attributes->merge(['class' => $clases]) }}>
  @if ($user->foto_url)
    <img src="{{ $user->foto_url }}" alt="{{ $alt }}" loading="lazy" decoding="async">
  @else
    <span aria-hidden="true">{{ $user->iniciales }}</span>
  @endif
</span>
