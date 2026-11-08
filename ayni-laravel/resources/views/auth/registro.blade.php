{{-- Formulario de registro (reemplaza a registro.php).
     @error / old() reemplazan a las funciones inv(), val() y msg() del prototipo. --}}
@extends('layouts.app')

@section('titulo', 'Registrarme')

@section('contenido')
  <main class="container py-4 py-lg-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-title fw-bold">Crea tu cuenta</h1>
        <p class="text-secondary">Primero tus datos; luego completas tu perfil según lo que necesites.</p>

        <form method="post" action="{{ route('registro.post') }}" class="card-ayni shadow-sm p-4" novalidate>
          @csrf

          {{-- Elección de rol --}}
          <fieldset class="mb-4">
            <legend class="h6">¿Qué quieres hacer en Ayni?</legend>
            <div class="row g-2">
              <div class="col-sm-6">
                <input type="radio" class="btn-check" name="rol" id="rol-cliente" value="cliente"
                       @checked(old('rol', 'cliente') === 'cliente')>
                <label class="btn btn-rol w-100 text-start p-3" for="rol-cliente">
                  <i class="bi bi-search"></i> <strong>Busco trabajadores</strong><br><small class="text-secondary">Soy cliente</small>
                </label>
              </div>
              <div class="col-sm-6">
                <input type="radio" class="btn-check" name="rol" id="rol-trabajador" value="trabajador"
                       @checked(old('rol') === 'trabajador')>
                <label class="btn btn-rol w-100 text-start p-3" for="rol-trabajador">
                  <i class="bi bi-tools"></i> <strong>Ofrezco mi servicio</strong><br><small class="text-secondary">Soy trabajador</small>
                </label>
              </div>
            </div>
          </fieldset>

          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="nombre">Nombre</label>
              <input id="nombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}">
              @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="apellidos">Apellidos</label>
              <input id="apellidos" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos') }}">
              @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="dni">DNI</label>
              <input id="dni" name="dni" class="form-control @error('dni') is-invalid @enderror"
                     inputmode="numeric" maxlength="8" value="{{ old('dni') }}">
              @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="telefono">Teléfono (WhatsApp)</label>
              <input id="telefono" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                     inputmode="numeric" maxlength="9" placeholder="9XXXXXXXX" value="{{ old('telefono') }}">
              @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="email">Correo</label>
              <input id="email" name="email" class="form-control @error('email') is-invalid @enderror" type="email" value="{{ old('email') }}">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="distrito_id">Distrito</label>
              <select id="distrito_id" name="distrito_id" class="form-select @error('distrito_id') is-invalid @enderror">
                <option value="">Elige un distrito</option>
                @foreach ($distritos as $d)
                  <option value="{{ $d->id }}" @selected(old('distrito_id') == $d->id)>{{ $d->nombre }}</option>
                @endforeach
              </select>
              @error('distrito_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="clave">Contraseña</label>
              <input id="clave" name="clave" class="form-control @error('clave') is-invalid @enderror" type="password">
              @error('clave')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="clave2">Repite la contraseña</label>
              <input id="clave2" name="clave2" class="form-control @error('clave2') is-invalid @enderror" type="password">
              @error('clave2')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12"><button class="btn btn-ayni px-4" type="submit">Crear cuenta</button></div>
          </div>
        </form>

        <p class="text-center mt-3 mb-0">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresar</a></p>
      </div>
    </div>
  </main>
@endsection