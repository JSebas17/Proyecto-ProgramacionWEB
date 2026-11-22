<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Oficio;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Support\Carbon;
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
                // Datos de las gráficas (se cachean junto con las cifras)
                'distribucion'  => Calificacion::distribucion(),
                'por_oficio'    => Oficio::withCount('perfiles')->orderByDesc('perfiles_count')->orderBy('nombre')
                                      ->get()->pluck('perfiles_count', 'nombre')->all(),
                // Datos extra del dashboard (actividad mensual y ranking); también cacheados
                'por_mes'       => $this->actividadMensual(),
                'ranking'       => $this->ranking(),
            ];
        });

        return view('admin.panel', compact('metricas'));
    }

    /**
     * Calificaciones y registros nuevos de los últimos 6 meses (para la gráfica de líneas).
     * Se agrupa en PHP para que funcione igual en SQLite y MySQL.
     *
     * @return array{etiquetas: string[], calificaciones: int[], registros: int[]}
     */
    private function actividadMensual(): array
    {
        $inicio = Carbon::now()->startOfMonth()->subMonths(5);
        $porMes = fn ($fechas) => collect($fechas)->countBy(fn ($f) => Carbon::parse($f)->format('Y-m'));

        $cal = $porMes(Calificacion::where('created_at', '>=', $inicio)->pluck('created_at'));
        $reg = $porMes(User::where('created_at', '>=', $inicio)->pluck('created_at'));

        $etiquetas = $calificaciones = $registros = [];
        for ($i = 0; $i < 6; $i++) {
            $mes = $inicio->copy()->addMonths($i);
            $etiquetas[]       = ucfirst($mes->translatedFormat('M'));
            $calificaciones[]  = (int) ($cal[$mes->format('Y-m')] ?? 0);
            $registros[]       = (int) ($reg[$mes->format('Y-m')] ?? 0);
        }

        return compact('etiquetas', 'calificaciones', 'registros');
    }

    /**
     * Los 5 trabajadores mejor calificados (tabla "ranking" del dashboard).
     * Se guardan como arreglos simples, no como modelos, para que el caché sea liviano.
     *
     * @return array<int, array<string, mixed>>
     */
    private function ranking(): array
    {
        return User::trabajadores()
            ->with(['perfilTrabajador.oficio', 'distrito'])
            ->withAvg('calificacionesRecibidas', 'puntuacion')
            ->withCount('calificacionesRecibidas')
            ->orderByDesc('calificaciones_recibidas_avg_puntuacion')
            ->orderByDesc('calificaciones_recibidas_count')
            ->limit(5)
            ->get()
            ->map(fn (User $u) => [
                'id'         => $u->id,
                'nombre'     => $u->nombre_completo,
                'iniciales'  => $u->iniciales,
                'color'      => $u->color_avatar,
                'oficio'     => $u->perfilTrabajador?->oficio?->nombre,
                'distrito'   => $u->distrito?->nombre,
                'nota'       => round((float) $u->calificaciones_recibidas_avg_puntuacion, 1),
                'resenas'    => (int) $u->calificaciones_recibidas_count,
                'verificado' => (bool) $u->verificado,
            ])
            ->all();
    }
}
