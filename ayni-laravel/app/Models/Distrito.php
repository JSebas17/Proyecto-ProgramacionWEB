<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Distrito de cobertura (catálogo configurable). */
class Distrito extends Model
{
    protected $fillable = ['nombre', 'provincia'];

    /** Usuarios que pertenecen a este distrito. */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Servicios ofrecidos en este distrito. */
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }
}