{{-- Formulario de inicio de sesión (reemplaza a login.php). --}}
@extends('layouts.app')

@section('titulo', 'Ingresar')

@section('contenido')
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <h1 class="h2 fw-bold">Ingresar</h1>
        <p class="text-secondary">Entra con tu correo para contactar o publicar.</p>

        <form method="post" action="{{ route('login.post') }}" class="card-ayni shadow-sm p-4" novalidate>
          {{-- Token CSRF: protege contra envíos desde otros sitios --}}
          @csrf

          @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
          @enderror

          <div class="mb-3">
            <label class="form-label fw-semibold" for="email">Correo</label>
            {{-- old() conserva lo escrito si el login falla --}}
            <input id="email" name="email" class="form-control" type="email"
                   placeholder="tucorreo@ejemplo.com" value="{{ old('email') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="clave">Contraseña</label>
            <input id="clave" name="clave" class="form-control @error('clave') is-invalid @enderror" type="password" required>
            @error('clave')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <button class="btn btn-ayni w-100" type="submit">Ingresar</button>
        </form>

        <p class="text-center mt-3 mb-0">¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a></p>
      </div>
    </div>
  </main>
@endsection