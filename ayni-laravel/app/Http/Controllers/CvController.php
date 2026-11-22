<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Auditoria;
use App\Support\Documentos;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Entrega el CV (PDF) de un trabajador. El archivo es privado: solo lo ve su dueño y el administrador.
 * Para que cualquier usuario con sesión pueda verlo, basta cambiar la condición de abort_unless().
 */
class CvController extends Controller
{
    public function show(User $usuario)
{
    $yo = auth()->user();

    // El dueño y el admin siempre pueden. El público solo ve el CV de trabajadores con cuenta activa
    $esDuenoOAdmin = $yo && ($yo->id === $usuario->id || $yo->rol === 'admin');
    $esPublico     = $usuario->rol === 'trabajador' && $usuario->activo;

    abort_unless($esDuenoOAdmin || $esPublico, 404);
    abort_unless(Documentos::existe($usuario->cv), 404);

    Auditoria::registrar('cv.descargado', ['objetivo_id' => $usuario->id]);

    return Storage::disk('local')->response(
        $usuario->cv,
        'CV-' . Str::slug($usuario->nombre_completo) . '.pdf',
        [
            'Content-Type'           => 'application/pdf',
            'Cache-Control'          => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ],
        'inline'
    );
}
}
