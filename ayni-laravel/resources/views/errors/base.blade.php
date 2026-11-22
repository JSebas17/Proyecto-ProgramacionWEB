@extends('layouts.app')
@section('titulo', $titulo)
@section('contenido')
  <main class="container py-5">
    <div class="row justify-content-center"><div class="col-md-8 col-lg-6 text-center">
      <div class="error-codigo" aria-hidden="true">{{ $codigo }}</div>
      <h1 class="h2 mt-2">{{ $titulo }}</h1>
      <p class="text-secondary">{{ $mensaje }}</p>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <a href="{{ route('home') }}" class="btn btn-ayni px-4">Volver al inicio</a>
        <a href="{{ route('home') }}#filtros" class="btn btn-outline-secondary">Buscar trabajo</a>
      </div>
    </div></div>
  </main>
@endsection
