<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Página de inicio: buscador de trabajadores con filtros.
 * Reemplaza a index.php + funciones de filtrado del prototipo.
 */
class HomeController extends Controller
{
    /** Ícono de Bootstrap Icons por oficio. */
    private const ICONOS = [
        'Electricista' => 'lightning-charge-fill', 'Pintura' => 'palette-fill',
        'Gasfitería' => 'droplet-fill', 'Carpintería' => 'hammer',
        'Hotelería' => 'building', 'Limpieza' => 'stars',
    ];

    /**
     * Lista trabajadores aplicando los filtros recibidos por GET.
     *
     * @param  Request  $request  Parámetros: q, distrito, oficio, verif, cuatro.
     */
    public function index(Request $request)
    {
        // "Solo verificados" viene activo la primera vez (sin parámetros en la URL)
        $verif  = $request->query() ? $request->boolean('verif') : true;
        $cuatro = $request->boolean('cuatro');
        $q      = trim((string) $request->query('q', ''));
        $distrito = $request->query('distrito');
        $oficio   = $request->query('oficio');

        $trabajadores = User::trabajadores()->activos()
            // Eager loading: evita consultas N+1 al mostrar oficio y distrito
            ->with(['perfilTrabajador.oficio', 'distrito'])
            // Promedio y cantidad de reseñas calculados en SQL
            ->withAvg('calificacionesRecibidas', 'puntuacion')
            ->withCount('calificacionesRecibidas')
            ->when($verif, fn ($c) => $c->verificados())
            ->when($distrito, fn ($c) => $c->whereHas('distrito', fn ($d) => $d->where('nombre', $distrito)))
            ->when($oficio, fn ($c) => $c->whereHas('perfilTrabajador.oficio', fn ($o) => $o->where('nombre', $oficio)))
            ->when($cuatro, fn ($c) => $c->having('calificaciones_recibidas_avg_puntuacion', '>=', 4))
            ->when($q !== '', function ($c) use ($q) {
                // Busca por nombre o por oficio (los parámetros enlazados evitan SQL injection)
                $c->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                      ->orWhere('apellidos', 'like', "%$q%")
                      ->orWhereHas('perfilTrabajador.oficio', fn ($o) => $o->where('nombre', 'like', "%$q%"));
                });
            })
            ->orderByDesc('calificaciones_recibidas_avg_puntuacion')
            ->paginate(12)
            ->withQueryString(); // conserva los filtros al cambiar de página

        return view('home', [
            'trabajadores' => $trabajadores,
            'distritos'    => Distrito::orderBy('nombre')->get(),
            'oficios'      => Oficio::orderBy('nombre')->get(),
            'iconos'       => self::ICONOS,
            'filtros'      => compact('q', 'distrito', 'oficio', 'verif', 'cuatro'),
        ]);
    }
}