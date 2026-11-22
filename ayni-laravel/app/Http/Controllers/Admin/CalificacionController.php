<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Cache;

/**
 * Moderación de calificaciones (comentarios ofensivos o falsos).
 */
class CalificacionController extends Controller
{
    /** Lista las calificaciones más recientes con autor y trabajador. */
    public function index()
    {
        $calificaciones = Calificacion::with(['autor', 'servicio.trabajador'])
            ->latest()
            ->paginate(15);

        return view('admin.calificaciones', compact('calificaciones'));
    }

    /** Elimina una calificación moderada. */
    public function destroy(Calificacion $calificacion)
    {
        $id = $calificacion->id;
        $calificacion->delete();
        Cache::forget('admin.metricas');
        Auditoria::registrar('admin.calificacion_eliminada', ['calificacion_id' => $id]);

        return back()->with('mensaje', 'Calificación eliminada.');
    }
}