<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Calificar a un trabajador (reemplaza a calificar.php).
 * Reglas de negocio: el admin no califica, nadie se califica a sí mismo
 * y cada usuario califica una sola vez cada servicio.
 */
class CalificacionController extends Controller
{
    /** Formulario de calificación; el trabajador debe estar activo y tener servicios. */
    public function create(User $trabajador)
    {
        if ($bloqueo = $this->bloqueo($trabajador)) {
            return $bloqueo;
        }

        $servicios = $trabajador->servicios()->activos()->get();

        return view('calificaciones.create', compact('trabajador', 'servicios'));
    }

    /**
     * Valida y guarda la calificación.
     * Los chips se filtran contra una lista cerrada (misma lista blanca del prototipo).
     */
    public function store(Request $request, User $trabajador)
    {
        if ($bloqueo = $this->bloqueo($trabajador)) {
            return $bloqueo;
        }

        $datos = $request->validate([
            // El servicio debe existir Y pertenecer a este trabajador
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where('trabajador_id', $trabajador->id)],
            'puntaje'     => ['required', 'integer', 'between:1,5'],
            'chips'       => ['nullable', 'array'],
            'chips.*'     => ['in:Puntual,Pagó lo acordado,Buen trato'],
            'comentario'  => ['nullable', 'string', 'max:500'],
        ], [
            'servicio_id.required' => 'Elige el servicio que quieres calificar.',
            'servicio_id.exists'   => 'Ese servicio no pertenece al trabajador.',
            'puntaje.required'     => 'Elige de 1 a 5 estrellas antes de enviar.',
            'puntaje.between'      => 'Elige de 1 a 5 estrellas antes de enviar.',
            'comentario.max'       => 'El comentario admite máximo 500 caracteres.',
        ]);

        // Una calificación por usuario y servicio
        $yaCalifico = Calificacion::where('servicio_id', $datos['servicio_id'])
            ->where('autor_id', $request->user()->id)->exists();

        if ($yaCalifico) {
            return back()->withInput()->with('error', 'Ya calificaste este servicio.');
        }

        Calificacion::create([
            'servicio_id' => $datos['servicio_id'],
            'autor_id'    => $request->user()->id,
            'puntuacion'  => $datos['puntaje'],
            'etiquetas'   => $datos['chips'] ?? [],
            'comentario'  => $datos['comentario'] ?? null,
        ]);

        return redirect()->route('trabajadores.show', $trabajador)
            ->with('mensaje', '¡Gracias! Tu calificación fue enviada.');
    }

    /**
     * Devuelve una redirección si no se puede calificar; null si todo está bien.
     */
    private function bloqueo(User $trabajador)
    {
        abort_unless($trabajador->rol === 'trabajador' && $trabajador->activo, 404);

        $yo = auth()->user();
        if ($yo->rol === 'admin' || $yo->id === $trabajador->id) {
            return redirect()->route('trabajadores.show', $trabajador)
                ->with('error', 'No puedes calificar este perfil.');
        }
        if (! $trabajador->servicios()->activos()->exists()) {
            return redirect()->route('trabajadores.show', $trabajador)
                ->with('error', 'Este trabajador aún no tiene servicios para calificar.');
        }
        return null;
    }
}
