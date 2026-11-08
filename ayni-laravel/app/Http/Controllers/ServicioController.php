<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\PerfilTrabajador;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD de servicios del trabajador (reemplaza a publicar.php).
 * Todas las rutas exigen sesión y rol "trabajador" (ver routes/web.php).
 */
class ServicioController extends Controller
{
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

        return redirect()->route('servicios.index')->with('mensaje', '¡Servicio publicado!');
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
            'oficios'   => Oficio::orderBy('nombre')->get(),
            'distritos' => Distrito::orderBy('nombre')->get(),
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
     * Mismas reglas del prototipo: tarifa > 0, hasta 4 fotos JPG/PNG/WEBP de 3 MB.
     */
    private function guardar(Request $request, ?Servicio $servicio = null): Servicio
    {
        $datos = $request->validate([
            'oficio_id'   => ['required', 'exists:oficios,id'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'tarifa'      => ['required', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fotos'       => ['nullable', 'array', 'max:4'],
            'fotos.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'], // KB
        ], [
            'oficio_id.required'   => 'Elige un oficio.',
            'distrito_id.required' => 'Elige un distrito.',
            'tarifa.required'      => 'Ingresa una tarifa mayor a 0.',
            'tarifa.integer'       => 'Ingresa una tarifa mayor a 0.',
            'tarifa.min'           => 'Ingresa una tarifa mayor a 0.',
            'fotos.max'            => 'Máximo 4 fotos.',
            'fotos.*.image'        => 'Cada archivo debe ser una imagen.',
            'fotos.*.mimes'        => 'Solo JPG, PNG o WEBP.',
            'fotos.*.max'          => 'Cada foto pesa máximo 3 MB.',
        ]);

        return DB::transaction(function () use ($request, $datos, $servicio) {
            $usuario = $request->user();

            // El oficio y la tarifa viven en el perfil 1:1 del trabajador
            PerfilTrabajador::updateOrCreate(['user_id' => $usuario->id], [
                'oficio_id'          => $datos['oficio_id'],
                'descripcion'        => $datos['descripcion'] ?? null,
                'tarifa_referencial' => $datos['tarifa'],
            ]);

            $campos = [
                'trabajador_id' => $usuario->id,
                'distrito_id'   => $datos['distrito_id'],
                'titulo'        => 'Servicio de ' . Oficio::find($datos['oficio_id'])->nombre,
                'descripcion'   => $datos['descripcion'] ?? '',
                'precio_desde'  => $datos['tarifa'],
            ];

            // Fotos nuevas: se guardan en el disco "public" con nombre aleatorio
            if ($request->hasFile('fotos')) {
                if ($servicio) {
                    Storage::disk('public')->delete($servicio->fotos ?? []); // reemplaza las anteriores
                }
                $campos['fotos'] = collect($request->file('fotos'))
                    ->map(fn ($f) => $f->store('uploads/servicios', 'public'))
                    ->all();
            }

            return $servicio
                ? tap($servicio)->update($campos)
                : Servicio::create($campos);
        });
    }
}
