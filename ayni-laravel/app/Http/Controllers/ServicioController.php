<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\PerfilTrabajador;
use App\Models\Servicio;
use App\Support\Imagenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * CRUD de servicios del trabajador.
 * Todas las rutas exigen sesión y rol "trabajador" (ver routes/web.php).
 */
class ServicioController extends Controller
{
    /** Máximo de fotos de trabajos por servicio. */
    private const MAX_FOTOS = 4;

    /** Lista los servicios del trabajador con su promedio de calificaciones. */
    public function index()
    {
        $servicios = auth()->user()->servicios()
            ->with('distrito')
            ->withAvg('calificaciones', 'puntuacion')
            ->withCount('calificaciones')
            ->latest()
            ->get();

        return view('servicios.index', compact('servicios'));
    }

    /** Formulario para publicar un servicio nuevo. */
    public function create()
    {
        return view('servicios.create', $this->catalogos());
    }

    /** Valida y crea el servicio. */
    public function store(Request $request)
    {
        $this->guardar($request);

        return redirect()->route('servicios.index')->with('mensaje', '¡Servicio publicado! Ya aparece en las búsquedas.');
    }

    /** Formulario de edición (solo el dueño). */
    public function edit(Servicio $servicio)
    {
        $this->autorizar($servicio);

        return view('servicios.edit', $this->catalogos() + ['servicio' => $servicio]);
    }

    /** Actualiza el servicio (solo el dueño). */
    public function update(Request $request, Servicio $servicio)
    {
        $this->autorizar($servicio);
        $this->guardar($request, $servicio);

        return redirect()->route('servicios.index')->with('mensaje', 'Servicio actualizado.');
    }

    /**
     * Desactiva el servicio (borrado lógico): deja de aparecer
     * pero conserva sus calificaciones.
     */
    public function destroy(Servicio $servicio)
    {
        $this->autorizar($servicio);
        $servicio->update(['estado' => 'inactivo']);

        return redirect()->route('servicios.index')->with('mensaje', 'Servicio desactivado.');
    }

    // ---------------------------------------------------------------

    /** Catálogos para los <select> del formulario. */
    private function catalogos(): array
    {
        return [
            'oficios'   => Oficio::catalogo(),
            'distritos' => Distrito::catalogo(),
        ];
    }

    /** Solo el trabajador dueño puede tocar su servicio; otro recibe 403. */
    private function autorizar(Servicio $servicio): void
    {
        abort_unless($servicio->trabajador_id === auth()->id(), 403);
    }

    /**
     * Valida el formulario y guarda servicio + perfil dentro de una transacción
     * (si algo falla, no queda nada a medias).
     *
     * Fotos: al editar se puede quitar cada foto actual (casilla "quitar[]") y agregar
     * nuevas; entre conservadas y nuevas el máximo es 4. Cada foto nueva se comprime.
     */
    private function guardar(Request $request, ?Servicio $servicio = null): Servicio
    {
        $datos = $request->validate([
            'oficio_id'   => ['required', 'exists:oficios,id'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'tarifa'      => ['required', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fotos'       => ['nullable', 'array'],
            'fotos.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'], // KB
            'quitar'      => ['nullable', 'array'],
            'quitar.*'    => ['string'],
        ], [
            'oficio_id.required'   => 'Elige un oficio.',
            'distrito_id.required' => 'Elige un distrito.',
            'tarifa.required'      => 'Ingresa una tarifa mayor a 0.',
            'tarifa.integer'       => 'Ingresa una tarifa mayor a 0.',
            'tarifa.min'           => 'Ingresa una tarifa mayor a 0.',
            'descripcion.max'      => 'La descripción admite máximo 1000 caracteres.',
            'fotos.*.image'        => 'Cada archivo debe ser una imagen.',
            'fotos.*.mimes'        => 'Solo JPG, PNG o WEBP.',
            'fotos.*.max'          => 'Cada foto pesa máximo 3 MB.',
        ]);

        // Fotos que ya tenía, menos las que marcó para quitar (solo se aceptan rutas propias)
        $actuales    = $servicio?->fotos ?? [];
        $paraQuitar  = array_values(array_intersect($datos['quitar'] ?? [], $actuales));
        $conservadas = array_values(array_diff($actuales, $paraQuitar));
        $subidas     = $request->file('fotos', []);

        if (count($conservadas) + count($subidas) > self::MAX_FOTOS) {
            throw ValidationException::withMessages([
                'fotos' => 'Máximo ' . self::MAX_FOTOS . ' fotos en total. Quita alguna actual o sube menos.',
            ]);
        }

        $nuevas = []; // rutas recién guardadas (para limpiarlas si algo falla)

        try {
            $guardado = DB::transaction(function () use ($request, $datos, $servicio, $conservadas, $subidas, &$nuevas) {
                $usuario = $request->user();

                // El oficio y la tarifa viven en el perfil 1:1 del trabajador
                PerfilTrabajador::updateOrCreate(['user_id' => $usuario->id], [
                    'oficio_id'          => $datos['oficio_id'],
                    'descripcion'        => $datos['descripcion'] ?? null,
                    'tarifa_referencial' => $datos['tarifa'],
                ]);

                foreach ($subidas as $archivo) {
                    $nuevas[] = Imagenes::trabajo($archivo);
                }

                $campos = [
                    'trabajador_id' => $usuario->id,
                    'distrito_id'   => $datos['distrito_id'],
                    'titulo'        => 'Servicio de ' . Oficio::findOrFail($datos['oficio_id'])->nombre,
                    'descripcion'   => $datos['descripcion'] ?? '',
                    'precio_desde'  => $datos['tarifa'],
                    'fotos'         => array_merge($conservadas, $nuevas),
                ];

                return $servicio
                    ? tap($servicio)->update($campos)
                    : Servicio::create($campos);
            });
        } catch (Throwable $e) {
            Imagenes::borrar($nuevas); // no dejar archivos huérfanos
            throw $e;
        }

        Imagenes::borrar($paraQuitar); // recién ahora se borran del disco las fotos quitadas

        return $guardado;
    }
}
