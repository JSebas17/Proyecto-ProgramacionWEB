<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Perfil profesional 1:1 de un usuario con rol trabajador. */
class PerfilTrabajador extends Model
{
    /** Nombre de tabla personalizado (no sigue el plural en inglés). */
    protected $table = 'perfiles_trabajador';

    /** La llave primaria es user_id, no un id autoincremental. */
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'oficio_id', 'descripcion',
        'anios_experiencia', 'tarifa_referencial', 'disponible', 'foto',
    ];

    protected function casts(): array
    {
        return ['disponible' => 'boolean', 'tarifa_referencial' => 'decimal:2'];
    }

    /** Usuario dueño de este perfil. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Oficio que ejerce el trabajador. */
    public function oficio(): BelongsTo
    {
        return $this->belongsTo(Oficio::class);
    }
}