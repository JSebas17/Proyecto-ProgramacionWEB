<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Perfil 1:1 de un usuario con rol cliente. */
class PerfilCliente extends Model
{
    protected $table = 'perfiles_cliente';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = ['user_id', 'preferencias_contacto', 'direccion_frecuente'];

    /** Usuario dueño de este perfil. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}