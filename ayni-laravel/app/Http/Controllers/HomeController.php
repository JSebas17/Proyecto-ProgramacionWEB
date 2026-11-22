<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Página de inicio: buscador de trabajadores con filtros y orden.
 */
class HomeController extends Controller
{
    /** Ícono de Bootstrap Icons por oficio (los oficios nuevos usan "tools"). */
    private const ICONOS = [
        'Electricista' => 'lightning-charge-fill', 'Pintura' => 'palette-fill',
        'Gasfitería' => 'droplet-fill', 'Carpintería' => 'hammer',
        'Hotelería' => 'building', 'Limpieza' => 'stars',
    ];

    /** Opciones del selector "Ordenar por". */
    public const ORDENES = [
        'mejor'     => 'Mejor calificados',
        'resenas'   => 'Más reseñas',
        'precio'    => 'Menor tarifa',
        'recientes' => 'Más recientes',
    ];

    /**
     * Lista trabajadores aplicando los filtros recibidos por GET.
     *
     * @param  Request  $request  Parámetros: q, distrito, oficio, verif, cuatro, orden.
     */
    public function index(Request $request)
    {
        // "Solo verificados" viene activo por defecto. Solo se apaga si llega verif=0
        // (el formulario envía ese 0 cuando el usuario desmarca la casilla). Así el filtro
        // sobrevive al cambiar de página, algo que fallaba antes.
        $verif  = $request->has('verif') ? $request->boolean('verif') : true;
        $cuatro = $request->boolean('cuatro');

        // Solo se aceptan textos: si alguien escribe ?q[]=x en la URL, se ignora en vez de romper la página
        $texto    = fn (string $clave): string => is_string($v = $request->query($clave)) ? trim($v) : '';
        // A05: se quitan los comodines de LIKE (% _ \) y se limita el largo; así "%" o "_" no
        // convierten la búsqueda en "traer todo" ni permiten consultas lentas a propósito
        $q        = mb_substr(str_replace(['%', '_', '\\'], '', $texto('q')), 0, 80);
        $distrito = $texto('distrito');
        $oficio   = $texto('oficio');
        $orden    = isset(self::ORDENES[$texto('orden')]) ? $texto('orden') : 'mejor';

        $consulta = User::trabajadores()->activos()
            ->whereHas('perfilTrabajador') // un trabajador aparece cuando ya completó su perfil
            // Eager loading: evita consultas N+1 al mostrar oficio y distrito
            ->with(['perfilTrabajador.oficio', 'distrito'])
            // Promedio y cantidad de reseñas calculados en SQL
            ->withAvg('calificacionesRecibidas', 'puntuacion')
            ->withCount('calificacionesRecibidas')
            ->when($verif, fn ($c) => $c->verificados())
            ->when($distrito !== '', fn ($c) => $c->whereHas('distrito', fn ($d) => $d->where('nombre', $distrito)))
            ->when($oficio !== '', fn ($c) => $c->whereHas('perfilTrabajador.oficio', fn ($o) => $o->where('nombre', $oficio)))
            // "4 o más": subconsulta (funciona igual en MySQL y SQLite; el HAVING anterior daba error 500)
            ->when($cuatro, fn ($c) => $c->whereRaw(
                '(select avg(c.puntuacion) from calificaciones c
                  join servicios s on s.id = c.servicio_id
                  where s.trabajador_id = users.id) >= 4'
            ))
            ->when($q !== '', function ($c) use ($q) {
                // Busca por nombre, apellidos u oficio (los parámetros enlazados evitan SQL injection)
                $c->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                      ->orWhere('apellidos', 'like', "%$q%")
                      ->orWhereHas('perfilTrabajador.oficio', fn ($o) => $o->where('nombre', 'like', "%$q%"));
                });
            });

        match ($orden) {
            'resenas'   => $consulta->orderByDesc('calificaciones_recibidas_count'),
            // Sin tarifa ("a convenir") al final
            'precio'    => $consulta->orderByRaw('(select tarifa_referencial from perfiles_trabajador where user_id = users.id) is null')
                                    ->orderByRaw('(select tarifa_referencial from perfiles_trabajador where user_id = users.id) asc'),
            'recientes' => $consulta->orderByDesc('users.id'),
            default     => $consulta->orderByDesc('calificaciones_recibidas_avg_puntuacion')
                                    ->orderByDesc('calificaciones_recibidas_count'),
        };

        return view('home', [
            'trabajadores' => $consulta->paginate(12)->withQueryString(), // conserva filtros al paginar
            // Catálogos desde caché: casi no cambian y se pedían en cada visita al inicio
            'distritos'    => Distrito::catalogo(),
            'oficios'      => Oficio::catalogo('id'), // orden del catálogo (como en el wireframe)
            'iconos'       => self::ICONOS,
            'ordenes'      => self::ORDENES,
            'filtros'      => compact('q', 'distrito', 'oficio', 'verif', 'cuatro', 'orden'),
        ]);
    }

    /**
     * Enlace único "Ofrecer mi servicio" del menú (como en el wireframe).
     * Cada tipo de visitante llega a donde realmente puede avanzar.
     */
    public function ofrecer(Request $request)
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('registro', ['rol' => 'trabajador']);
        }
        if ($usuario->rol === 'trabajador') {
            return redirect()->route('publicar');
        }

        return redirect()->route('home')
            ->with('error', 'Para ofrecer un servicio necesitas una cuenta de trabajador.');
    }
}
