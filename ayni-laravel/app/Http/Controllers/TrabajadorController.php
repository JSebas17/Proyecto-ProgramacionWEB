<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * Perfil público de un trabajador (reemplaza a perfil.php).
 */
class TrabajadorController extends Controller
{
    /**
     * Muestra datos, fotos y reseñas.
     * Route-model binding: Laravel busca el User por el id de la URL (404 si no existe).
     */
    public function show(User $trabajador)
    {
        // Solo se muestran trabajadores con cuenta activa
        abort_unless($trabajador->rol === 'trabajador' && $trabajador->activo, 404);

        // Eager loading de relaciones + promedio y conteo calculados en SQL
        $trabajador->load(['perfilTrabajador.oficio', 'distrito', 'servicios' => fn ($q) => $q->activos()])
            ->loadAvg('calificacionesRecibidas', 'puntuacion')
            ->loadCount('calificacionesRecibidas');

        // Últimas 20 reseñas. La columna se califica porque la relación hace JOIN con servicios.
        $resenas = $trabajador->calificacionesRecibidas()
            ->with('autor.distrito')
            ->latest('calificaciones.created_at')
            ->limit(20)
            ->get();

        // Fotos de todos sus servicios activos (máximo 8)
        $fotos = $trabajador->servicios->flatMap(fn ($s) => $s->fotos ?? [])->take(8);

        return view('trabajadores.show', compact('trabajador', 'resenas', 'fotos'));
    }
}