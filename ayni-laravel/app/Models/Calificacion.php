<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/** Calificación (1 a 5) que un usuario deja tras un servicio. */
class Calificacion extends Model
{
    /** Laravel pluralizaría "calificacions"; forzamos el nombre real. */
    protected $table = 'calificaciones';

    protected $fillable = ['servicio_id', 'autor_id', 'puntuacion', 'comentario', 'etiquetas'];

    /**
     * El panel del admin cachea sus cifras (10 min). Cada vez que cambian estos datos
     * se descarta ese caché, así el panel nunca muestra números viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin.metricas'));
        static::deleted(fn () => Cache::forget('admin.metricas'));
    }

    /**
     * Cuántas calificaciones hay por cada puntaje, de 5 a 1 (con ceros incluidos).
     * Con $trabajadorId cuenta solo las de ese trabajador; sin él, las de toda la plataforma.
     * Una sola consulta con GROUP BY: sirve para las gráficas del perfil y del panel admin.
     *
     * @return array<int,int>  [5 => n, 4 => n, 3 => n, 2 => n, 1 => n]
     */
    public static function distribucion(?int $trabajadorId = null): array
    {
        $consulta = static::query()
            ->selectRaw('calificaciones.puntuacion as estrellas, count(*) as total')
            ->groupBy('calificaciones.puntuacion');

        if ($trabajadorId) {
            $consulta->join('servicios', 'servicios.id', '=', 'calificaciones.servicio_id')
                     ->where('servicios.trabajador_id', $trabajadorId);
        }

        $cuenta = $consulta->pluck('total', 'estrellas');

        $resultado = [];
        foreach ([5, 4, 3, 2, 1] as $estrellas) {
            $resultado[$estrellas] = (int) ($cuenta[$estrellas] ?? 0);
        }
        return $resultado;
    }

    /** La columna JSON "etiquetas" se maneja como array. */
protected function casts(): array
{
    return ['etiquetas' => 'array'];
}
    /** Servicio calificado. */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /** Usuario que escribió la calificación. */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}