<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\PerfilCliente;
use App\Models\PerfilTrabajador;
use App\Support\Auditoria;
use App\Support\Documentos;
use App\Support\Imagenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Mi perfil": cualquier usuario con sesión edita sus datos y su foto de perfil.
 *  - Trabajador: además oficio, tarifa, años de experiencia, descripción y disponibilidad.
 *  - Cliente: además sus preferencias de contacto y dirección frecuente.
 * También permite desactivar la propia cuenta (borrado lógico, como indica el informe).
 */
class PerfilController extends Controller
{
    /** Formulario de edición del perfil propio. */
    public function edit(Request $request)
    {
        $usuario = $request->user()->load(['perfilTrabajador.oficio', 'perfilCliente', 'distrito']);

        return view('perfil.edit', [
            'usuario'   => $usuario,
            'distritos' => Distrito::catalogo(),
            'oficios'   => Oficio::catalogo(),
        ]);
    }

    /** Valida y guarda los cambios. Todo en una transacción. */
    public function update(Request $request)
    {
        $usuario = $request->user();
        $rol     = $usuario->rol;

        $reglas = [
            'nombre'      => ['required', 'string', 'max:100'],
            'apellidos'   => ['required', 'string', 'max:100'],
            // El admin no está obligado a tener teléfono; clientes y trabajadores sí (WhatsApp)
            'telefono'    => [$rol === 'admin' ? 'nullable' : 'required', 'regex:/^(51)?9[0-9]{8}$/'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'quitar_foto' => ['nullable', 'boolean'],
        ];

        if ($rol === 'trabajador') {
            $reglas += [
                'oficio_id'         => ['required', 'exists:oficios,id'],
                'tarifa'            => ['nullable', 'integer', 'min:1'],
                'anios_experiencia' => ['nullable', 'integer', 'between:0,60'],
                'descripcion'       => ['nullable', 'string', 'max:1000'],
                'disponible'        => ['nullable', 'boolean'],
                'cv'                => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
                'quitar_cv'         => ['nullable', 'boolean'],
            ];
        }
        if ($rol === 'cliente') {
            $reglas += [
                'preferencias_contacto' => ['nullable', 'string', 'max:255'],
                'direccion_frecuente'   => ['nullable', 'string', 'max:255'],
            ];
        }

        $datos = $request->validate($reglas, [
            'nombre.required'      => 'Escribe tu nombre.',
            'apellidos.required'   => 'Escribe tus apellidos.',
            'telefono.required'    => 'Escribe 9 números, empezando con 9.',
            'telefono.regex'       => 'Escribe 9 números, empezando con 9.',
            'distrito_id.required' => 'Elige tu distrito.',
            'distrito_id.exists'   => 'Elige tu distrito.',
            'foto.image'           => 'El archivo debe ser una imagen.',
            'foto.mimes'           => 'Solo JPG, PNG o WEBP.',
            'foto.max'             => 'La foto pesa máximo 3 MB.',
            'cv.mimes'             => 'El CV debe ser un archivo PDF.',
            'cv.max'               => 'El CV pesa máximo 5 MB.',
            'oficio_id.required'   => 'Elige tu oficio.',
            'oficio_id.exists'     => 'Elige tu oficio.',
            'tarifa.integer'       => 'Ingresa una tarifa mayor a 0 o déjala vacía para "a convenir".',
            'tarifa.min'           => 'Ingresa una tarifa mayor a 0 o déjala vacía para "a convenir".',
            'anios_experiencia.integer' => 'Escribe los años con un número (0 a 60).',
            'anios_experiencia.between' => 'Escribe los años con un número (0 a 60).',
            'descripcion.max'      => 'La descripción admite máximo 1000 caracteres.',
        ]);

        $fotoVieja = $usuario->foto;
        $fotoNueva = null;

        if ($request->hasFile('foto')) {
            $fotoNueva = Imagenes::avatar($request->file('foto'));
        }

        // CV (solo trabajadores): subir uno nuevo reemplaza al anterior; "quitar_cv" lo borra
        $cvViejo = $usuario->cv;
        $cvNuevo = null;

        if ($rol === 'trabajador' && $request->hasFile('cv')) {
            try {
                $cvNuevo = Documentos::guardarCv($request->file('cv'));
            } catch (\Throwable $e) {
                Imagenes::borrar($fotoNueva); // no dejar la foto recién subida huérfana
                throw $e;
            }
        }
        $cambiaCv = $cvNuevo !== null || ($rol === 'trabajador' && $request->boolean('quitar_cv') && $cvViejo);

        // true = hay que borrar la foto actual del disco al terminar
        $reemplazar = $fotoNueva !== null || ($request->boolean('quitar_foto') && $fotoVieja);

        try {
            DB::transaction(function () use ($usuario, $datos, $request, $rol, $fotoNueva, $reemplazar, $cvNuevo, $cambiaCv) {
                $cambios = [
                    'name'        => $datos['nombre'],
                    'apellidos'   => $datos['apellidos'],
                    'telefono'    => $datos['telefono'] ?? null,
                    'distrito_id' => $datos['distrito_id'],
                ];
                if ($reemplazar) {
                    $cambios['foto'] = $fotoNueva; // null cuando solo se quitó
                }
                if ($cambiaCv) {
                    $cambios['cv'] = $cvNuevo; // null cuando solo se quitó
                }
                $usuario->update($cambios);

                if ($rol === 'trabajador') {
                    PerfilTrabajador::updateOrCreate(['user_id' => $usuario->id], [
                        'oficio_id'          => $datos['oficio_id'],
                        'tarifa_referencial' => $datos['tarifa'] ?? null,
                        'anios_experiencia'  => $datos['anios_experiencia'] ?? 0,
                        'descripcion'        => $datos['descripcion'] ?? null,
                        'disponible'         => $request->boolean('disponible'),
                    ]);
                }
                if ($rol === 'cliente') {
                    PerfilCliente::updateOrCreate(['user_id' => $usuario->id], [
                        'preferencias_contacto' => $datos['preferencias_contacto'] ?? null,
                        'direccion_frecuente'   => $datos['direccion_frecuente'] ?? null,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Imagenes::borrar($fotoNueva);
            Documentos::borrar($cvNuevo);
            throw $e;
        }

        if ($reemplazar) {
            Imagenes::borrar($fotoVieja);
        }
        if ($cambiaCv) {
            Documentos::borrar($cvViejo);
        }

        return redirect()->route('perfil.edit')->with('mensaje', 'Tus cambios se guardaron.');
    }

    /**
     * Desactiva la cuenta propia (borrado lógico). Pide la contraseña para confirmar.
     * Sus servicios dejan de verse porque el listado solo muestra cuentas activas.
     */
    public function destroy(Request $request)
    {
        abort_if($request->user()->rol === 'admin', 403, 'La cuenta de administrador no se puede desactivar aquí.');

        $request->validate(
            ['clave_actual' => ['required', 'current_password']],
            [
                'clave_actual.required'         => 'Escribe tu contraseña para confirmar.',
                'clave_actual.current_password' => 'La contraseña no es correcta.',
            ]
        );

        $usuario = $request->user();
        $usuario->update(['activo' => false]);
        Auditoria::registrar('cuenta.desactivada_por_el_usuario');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('mensaje', 'Tu cuenta fue desactivada y tus servicios dejaron de mostrarse.');
    }
}
