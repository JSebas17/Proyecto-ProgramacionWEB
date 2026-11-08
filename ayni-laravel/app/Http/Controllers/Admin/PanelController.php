<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Panel de inicio del administrador: métricas de impacto social.
 * Las cifras coinciden con la tabla "Métricas proyectadas" del informe.
 */
class PanelController extends Controller
{
    /** Muestra contadores; se cachean 10 minutos para no consultar en cada visita. */
    public function index()
    {
        $metricas = Cache::remember('admin.metricas', 600, function () {
            $trabajadores = User::trabajadores();
            return [
                'trabajadores'  => (clone $trabajadores)->count(),
                'verificados'   => (clone $trabajadores)->verificados()->count(),
                'pendientes'    => (clone $trabajadores)->where('verificado', false)->count(),
                'clientes'      => User::where('rol', 'cliente')->count(),
                'servicios'     => Servicio::activos()->count(),
                'calificaciones' => Calificacion::count(),
                'promedio'      => round((float) Calificacion::avg('puntuacion'), 1),
            ];
        });

        return view('admin.panel', compact('metricas'));
    }
}