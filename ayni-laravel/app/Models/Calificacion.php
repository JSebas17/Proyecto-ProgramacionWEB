<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Calificación (1 a 5) que un usuario deja tras un servicio. */
class Calificacion extends Model
{
    /** Laravel pluralizaría "calificacions"; forzamos el nombre real. */
    protected $table = 'calificaciones';

    protected $fillable = ['servicio_id', 'autor_id', 'puntuacion', 'comentario', 'etiquetas'];

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