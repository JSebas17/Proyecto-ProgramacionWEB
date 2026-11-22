{{-- Formulario de registro con foto de perfil opcional.
     @error / old() conservan lo escrito si hay errores de validación. --}}
@extends('layouts.app')

@section('titulo', 'Registrarme')

@section('contenido')
  <main class="container py-4 py-lg-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-title mb-2">Crea tu cuenta</h1>
        <p class="text-secondary">Primero tus datos; luego completas tu perfil según lo que necesites.</p>

        {{-- enctype multipart: necesario para enviar la foto --}}
        <form method="post" action="{{ route('registro.post') }}" enctype="multipart/form-data"
              class="card-ayni p-4" novalidate data-bloquear>
          @csrf

          {{-- Elección de rol (?rol=trabajador desde "Ofrecer mi servicio" deja marcada la segunda opción) --}}
          <fieldset class="mb-4">
            <legend class="h6">¿Qué quieres hacer en Ayni?</legend>
            <div class="row g-2">
              <div class="col-sm-6">
                <input type="radio" class="btn-check" name="rol" id="rol-cliente" value="cliente"
                       @checked(old('rol', $rolInicial) === 'cliente')>
                <label class="btn btn-rol w-100 text-start p-3" for="rol-cliente">
                  <i class="bi bi-search" aria-hidden="true"></i> <strong>Busco trabajadores</strong><br><small class="text-secondary">Soy cliente</small>
                </label>
              </div>
              <div class="col-sm-6">
                <input type="radio" class="btn-check" name="rol" id="rol-trabajador" value="trabajador"
                       @checked(old('rol', $rolInicial) === 'trabajador')>
                <label class="btn btn-rol w-100 text-start p-3" for="rol-trabajador">
                  <i class="bi bi-tools" aria-hidden="true"></i> <strong>Ofrezco mi servicio</strong><br><small class="text-secondary">Soy trabajador</small>
                </label>
              </div>
            </div>
            @error('rol')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </fieldset>

          {{-- Foto de perfil (opcional) --}}
          <div class="foto-picker mb-4" data-foto>
            <span class="avatar avatar-lg avatar-c0" data-foto-vista data-iniciales="?">
              <span aria-hidden="true"><i class="bi bi-camera"></i></span>
            </span>
            <div class="acciones">
              <label class="btn btn-outline-secondary btn-sm" for="foto"><i class="bi bi-camera" aria-hidden="true"></i> Subir foto de perfil</label>
              <input id="foto" name="foto" type="file" class="visually-hidden" accept="image/jpeg,image/png,image/webp" data-foto-input>
              <small class="text-secondary">Opcional. Con una foto clara los clientes confían más. JPG, PNG o WEBP, máx. 3 MB.</small>
              <div class="text-danger small" data-foto-aviso role="alert" hidden></div>
              @error('foto')<div class="text-danger small" role="alert">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="nombre">Nombre</label>
              <input id="nombre" name="nombre" autocomplete="given-name" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}">
              @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="apellidos">Apellidos</label>
              <input id="apellidos" name="apellidos" autocomplete="family-name" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos') }}">
              @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="dni">DNI</label>
              <input id="dni" name="dni" class="form-control @error('dni') is-invalid @enderror"
                     inputmode="numeric" maxlength="8" value="{{ old('dni') }}">
              <div class="form-text">Solo lo ve el administrador para verificar tu identidad.</div>
              @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="telefono">Teléfono (WhatsApp)</label>
              <input id="telefono" name="telefono" autocomplete="tel-national" class="form-control @error('telefono') is-invalid @enderror"
                     inputmode="numeric" maxlength="9" placeholder="9XXXXXXXX" value="{{ old('telefono') }}">
              @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="email">Correo</label>
              <input id="email" name="email" autocomplete="email" class="form-control @error('email') is-invalid @enderror" type="email" value="{{ old('email') }}">
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
            {{-- CV: solo aparece si eliges "Ofrezco mi servicio" (public/js/cv.js) --}}
            <div class="col-12" data-cv data-solo-trabajador @if (old('rol', $rolInicial) !== 'trabajador') hidden @endif>
              <label class="form-label fw-semibold" for="cv">Currículum (CV) <span class="text-secondary fw-normal">· opcional</span></label>
              <input id="cv" name="cv" type="file" accept="application/pdf,.pdf" class="form-control @error('cv') is-invalid @enderror" data-cv-input>
              <div class="form-text">Solo PDF, máximo 5 MB.</div>
              <div class="text-danger small" data-cv-aviso role="alert" hidden></div>
              @error('cv')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="clave">Contraseña</label>
              <input id="clave" name="clave" autocomplete="new-password" class="form-control @error('clave') is-invalid @enderror" type="password">
              <div class="form-text">Mínimo 8 caracteres.</div>
              @error('clave')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="clave2">Repite la contraseña</label>
              <input id="clave2" name="clave2" autocomplete="new-password" class="form-control @error('clave2') is-invalid @enderror" type="password">
              @error('clave2')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input @error('acepto') is-invalid @enderror" type="checkbox" name="acepto" id="acepto" value="1" @checked(old('acepto'))>
                <label class="form-check-label" for="acepto">
                  Acepto que Ayni use mis datos solo para conectarme con clientes o trabajadores. Mi DNI no se muestra públicamente.
                </label>
                @error('acepto')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-12"><button class="btn btn-ayni px-4 py-2" type="submit">Crear cuenta</button></div>
          </div>
        </form>

        <p class="text-center mt-3 mb-0">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresar</a></p>
      </div>
    </div>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('js/foto.js') }}"></script>
  <script src="{{ asset('js/cv.js') }}"></script>
@endpush
