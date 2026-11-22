{{-- Mi perfil: foto, datos personales y datos propios del rol. Vista previa de la tarjeta en vivo. --}}
@extends('layouts.app')
@section('titulo', 'Mi perfil')

@section('contenido')
@php
  $perfilT = $usuario->perfilTrabajador;
  $perfilC = $usuario->perfilCliente;
  $esTrabajador = $usuario->rol === 'trabajador';
  $esCliente    = $usuario->rol === 'cliente';
@endphp
<main class="container py-4 py-lg-5">
  <x-migas :items="[['Inicio', route('home')], ['Mi perfil']]" />
  <div class="row g-4">
    <div class="col-lg-7">
      <h1 class="display-title mb-2">Mi perfil</h1>
      <p class="text-secondary">
        @if ($esTrabajador) Un perfil completo y con foto recibe más contactos.
        @elseif ($esCliente) Tu foto y tus datos ayudan a que los trabajadores confíen en ti.
        @else Datos de tu cuenta de administración. @endif
      </p>

      <form method="post" action="{{ route('perfil.update') }}" enctype="multipart/form-data" id="form-perfil"
            class="card-ayni p-4" novalidate data-bloquear>
        @csrf @method('PUT')

        {{-- Foto de perfil --}}
        <div class="foto-picker mb-4" data-foto>
          <x-avatar :user="$usuario" size="xl" data-foto-vista :data-iniciales="$usuario->iniciales" />
          <div class="acciones">
            <label class="btn btn-outline-secondary btn-sm" for="foto"><i class="bi bi-camera" aria-hidden="true"></i> {{ $usuario->foto ? 'Cambiar foto' : 'Subir foto' }}</label>
            <input id="foto" name="foto" type="file" class="visually-hidden" accept="image/jpeg,image/png,image/webp" data-foto-input>
            @if ($usuario->foto)
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="quitar_foto" id="quitar_foto" value="1" data-foto-quitar>
                <label class="form-check-label small" for="quitar_foto">Quitar mi foto actual</label>
              </div>
            @endif
            <small class="text-secondary">JPG, PNG o WEBP, máx. 3 MB. Se recorta en cuadrado y se comprime sola.</small>
            <div class="text-danger small" data-foto-aviso role="alert" hidden></div>
            @error('foto')<div class="text-danger small" role="alert">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-semibold" for="nombre">Nombre</label>
            <input id="nombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $usuario->name) }}">
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-semibold" for="apellidos">Apellidos</label>
            <input id="apellidos" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $usuario->apellidos) }}">
            @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-semibold" for="telefono">Teléfono (WhatsApp)</label>
            <input id="telefono" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                   inputmode="numeric" maxlength="11" placeholder="9XXXXXXXX" value="{{ old('telefono', $usuario->telefono) }}">
            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-semibold" for="distrito_id">Distrito</label>
            <select id="distrito_id" name="distrito_id" class="form-select @error('distrito_id') is-invalid @enderror">
              @foreach ($distritos as $d)
                <option value="{{ $d->id }}" @selected(old('distrito_id', $usuario->distrito_id) == $d->id)>{{ $d->nombre }}</option>
              @endforeach
            </select>
            @error('distrito_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" for="correo">Correo</label>
            <input id="correo" class="form-control" value="{{ $usuario->email }}" disabled>
            <div class="form-text">El correo y el DNI no se cambian aquí. Si hay un error, avisa al administrador.</div>
          </div>

          @if ($esTrabajador)
            <div class="col-12"><hr class="my-1"><h2 class="h5 mt-3 mb-0">Tu trabajo</h2></div>
            <div class="col-sm-5">
              <label class="form-label fw-semibold" for="oficio_id">Oficio</label>
              <select id="oficio_id" name="oficio_id" class="form-select @error('oficio_id') is-invalid @enderror">
                @foreach ($oficios as $o)
                  <option value="{{ $o->id }}" @selected(old('oficio_id', $perfilT?->oficio_id) == $o->id)>{{ $o->nombre }}</option>
                @endforeach
              </select>
              @error('oficio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-3">
              <label class="form-label fw-semibold" for="tarifa">Tarifa (S/)</label>
              <input id="tarifa" name="tarifa" type="number" min="1" inputmode="numeric" placeholder="A convenir"
                     class="form-control @error('tarifa') is-invalid @enderror"
                     value="{{ old('tarifa', $perfilT?->tarifa_referencial ? (int) $perfilT->tarifa_referencial : '') }}">
              @error('tarifa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
              <label class="form-label fw-semibold" for="anios_experiencia">Años de experiencia</label>
              <input id="anios_experiencia" name="anios_experiencia" type="number" min="0" max="60" inputmode="numeric"
                     class="form-control @error('anios_experiencia') is-invalid @enderror"
                     value="{{ old('anios_experiencia', $perfilT?->anios_experiencia ?? 0) }}">
              @error('anios_experiencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" for="descripcion">Sobre mi trabajo</label>
              <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000"
                        class="form-control @error('descripcion') is-invalid @enderror"
                        placeholder="Qué trabajos haces, en qué zonas, qué experiencia tienes…">{{ old('descripcion', $perfilT?->descripcion) }}</textarea>
              @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="disponible" id="disponible" value="1"
                       @checked(old('disponible', $perfilT?->disponible ?? true))>
                <label class="form-check-label" for="disponible">Estoy disponible para nuevos trabajos</label>
              </div>
            </div>
            <div class="col-12" data-cv>
              <label class="form-label fw-semibold" for="cv">Currículum (CV) en PDF</label>
              @if ($usuario->cv)
                <div class="mb-2 small">
                  <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                  <a href="{{ route('cv.descargar', $usuario) }}" target="_blank" rel="noopener">Ver mi CV actual</a>
                  <span class="form-check d-inline-block ms-3">
                    <input class="form-check-input" type="checkbox" name="quitar_cv" id="quitar_cv" value="1">
                    <label class="form-check-label" for="quitar_cv">Quitar mi CV</label>
                  </span>
                </div>
              @endif
              <input id="cv" name="cv" type="file" accept="application/pdf,.pdf" class="form-control @error('cv') is-invalid @enderror" data-cv-input>
              <div class="form-text">{{ $usuario->cv ? 'Si subes uno nuevo, reemplaza al actual. ' : '' }}Solo PDF, máximo 5 MB. Lo ven únicamente tú y el administrador.</div>
              <div class="text-danger small" data-cv-aviso role="alert" hidden></div>
              @error('cv')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          @if ($esCliente)
            <div class="col-12"><hr class="my-1"><h2 class="h5 mt-3 mb-0">Cómo prefieres que te contacten</h2></div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="preferencias_contacto">Preferencia de contacto</label>
              <input id="preferencias_contacto" name="preferencias_contacto" maxlength="255" placeholder="WhatsApp, por las tardes…"
                     class="form-control @error('preferencias_contacto') is-invalid @enderror"
                     value="{{ old('preferencias_contacto', $perfilC?->preferencias_contacto) }}">
              @error('preferencias_contacto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="direccion_frecuente">Zona o dirección frecuente</label>
              <input id="direccion_frecuente" name="direccion_frecuente" maxlength="255" placeholder="Wanchaq, cerca al mercado"
                     class="form-control @error('direccion_frecuente') is-invalid @enderror"
                     value="{{ old('direccion_frecuente', $perfilC?->direccion_frecuente) }}">
              @error('direccion_frecuente')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          <div class="col-12 d-flex flex-wrap gap-2 mt-4">
            <button class="btn btn-ayni px-4 py-2" type="submit">Guardar cambios</button>
            <a href="{{ $esTrabajador ? route('trabajadores.show', $usuario) : route('home') }}" class="btn btn-outline-secondary py-2">
              {{ $esTrabajador ? 'Ver mi perfil público' : 'Volver al inicio' }}
            </a>
          </div>
        </div>
      </form>

      {{-- Desactivar cuenta (borrado lógico). No aplica al administrador. --}}
      @unless ($usuario->rol === 'admin')
        <details class="card-ayni p-4 mt-4" @if ($errors->has('clave_actual')) open @endif>
          <summary class="fw-semibold">Desactivar mi cuenta</summary>
          <p class="text-secondary mt-3 mb-3">
            @if ($esTrabajador) Tu perfil y tus servicios dejarán de mostrarse. Tus calificaciones se conservan. @else Tu cuenta dejará de poder ingresar. @endif
            Escribe tu contraseña para confirmar.
          </p>
          <form method="post" action="{{ route('perfil.destroy') }}" class="row g-2 align-items-start" data-confirmar="¿Seguro que quieres desactivar tu cuenta?">
            @csrf @method('DELETE')
            <div class="col-sm-7">
              <label class="visually-hidden" for="clave_actual">Contraseña actual</label>
              <input id="clave_actual" name="clave_actual" type="password" autocomplete="current-password" placeholder="Tu contraseña"
                     class="form-control @error('clave_actual') is-invalid @enderror">
              @error('clave_actual')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-5 d-grid"><button class="btn btn-outline-danger" type="submit">Desactivar cuenta</button></div>
          </form>
        </details>
      @endunless
    </div>

    {{-- Vista previa en vivo: la rellena public/js/perfil.js --}}
    <aside class="col-lg-5">
      <div class="card-ayni p-4 sticky-side">
        <h2 class="h5 mb-3">{{ $esTrabajador ? 'Así te ven los clientes' : 'Así se ve tu perfil' }}</h2>
        <div class="tarjeta">
          <div class="d-flex gap-3 align-items-center">
            <x-avatar :user="$usuario" data-preview-avatar />
            <div>
              <h2 id="v-nombre">{{ $usuario->nombre_completo }}</h2>
              <div class="sub" id="v-sub"></div>
            </div>
          </div>
          @if ($usuario->verificado)
            <span class="badge badge-verified rounded-pill align-self-start mt-2"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Identidad verificada</span>
          @elseif ($esTrabajador)
            <span class="small text-secondary mt-2"><i class="bi bi-hourglass-split" aria-hidden="true"></i> Verificación pendiente del administrador</span>
          @endif
          @if ($esTrabajador)
            <div class="d-flex justify-content-between align-items-baseline mt-3 pt-2 border-top">
              <span class="estado-ocupado" id="v-estado" hidden><i class="bi bi-clock" aria-hidden="true"></i> Ocupado ahora</span>
              <span class="precio ms-auto" id="v-precio"></span>
            </div>
          @endif
        </div>
        @if ($esTrabajador)
          <a class="btn btn-outline-secondary w-100 mt-3" href="{{ route('servicios.index') }}"><i class="bi bi-briefcase" aria-hidden="true"></i> Mis servicios</a>
        @endif
      </div>
    </aside>
  </div>
</main>
@endsection

@push('scripts')
  <script src="{{ asset('js/foto.js') }}"></script>
  <script src="{{ asset('js/perfil.js') }}"></script>
  <script src="{{ asset('js/cv.js') }}"></script>
@endpush
