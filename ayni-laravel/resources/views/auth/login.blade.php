{{-- Formulario de inicio de sesión. --}}
@extends('layouts.app')

@section('titulo', 'Ingresar')

@section('contenido')
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <h1 class="h2 mb-2">Ingresar</h1>
        {{-- Si venía de otra página (p. ej. "Calificar"), se le avisa que volverá ahí --}}
        <p class="text-secondary">
          {{ session('url.intended') ? 'Ingresa para continuar donde estabas.' : 'Entra con tu correo para contactar o publicar.' }}
        </p>

        <form method="post" action="{{ route('login.post') }}" class="card-ayni p-4" novalidate data-bloquear>
          {{-- Token CSRF: protege contra envíos desde otros sitios --}}
          @csrf

          @error('email')
            <div class="alert alert-danger" role="alert">{{ $message }}</div>
          @enderror

          <div class="mb-3">
            <label class="form-label fw-semibold" for="email">Correo</label>
            {{-- old() conserva lo escrito si el login falla --}}
            <input id="email" name="email" class="form-control" type="email" autocomplete="email"
                   placeholder="tucorreo@ejemplo.com" value="{{ old('email') }}" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="clave">Contraseña</label>
            <input id="clave" name="clave" class="form-control @error('clave') is-invalid @enderror" type="password"
                   autocomplete="current-password" required>
            @error('clave')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="recordar" id="recordar" value="1">
            <label class="form-check-label" for="recordar">Mantener mi sesión iniciada</label>
          </div>
          <button class="btn btn-ayni w-100 py-2" type="submit">Ingresar</button>
        </form>

        <p class="text-center mt-3 mb-0">¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a></p>
      </div>
    </div>
  </main>
@endsection
