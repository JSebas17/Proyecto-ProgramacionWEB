<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/** Servicio publicado por un trabajador. */
class Servicio extends Model
{
    protected $table = 'servicios';

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
     * IMPORTANTE: "fotos" debe estar aquí. Si no, Laravel descarta la columna en
     * silencio al hacer create()/update() y las fotos de los trabajos nunca se guardan.
     */
    protected $fillable = [
        'trabajador_id', 'distrito_id', 'titulo',
        'descripcion', 'precio_desde', 'fotos', 'estado',
    ];

    /** Convierte la columna JSON "fotos" en array de PHP y viceversa. */
    protected function casts(): array
    {
        return ['fotos' => 'array', 'precio_desde' => 'decimal:2'];
    }

    /** Trabajador que publica el servicio. */
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    /** Distrito donde se ofrece. */
    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    /** Calificaciones recibidas por este servicio. */
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    /** Scope: solo servicios activos. */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
