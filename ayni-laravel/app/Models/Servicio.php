<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Servicio publicado por un trabajador. */
class Servicio extends Model
{

    /** Convierte la columna JSON "fotos" en array de PHP y viceversa. */
protected function casts(): array
{
    return ['fotos' => 'array', 'precio_desde' => 'decimal:2'];
}
    protected $table = 'servicios';

    protected $fillable = [
        'trabajador_id', 'distrito_id', 'titulo',
        'descripcion', 'precio_desde', 'estado',
    ];

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