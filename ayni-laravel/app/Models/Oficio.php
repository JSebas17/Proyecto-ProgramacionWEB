<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Oficio (electricista, pintor, gasfitero...). */
class Oficio extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    /** Perfiles de trabajadores que ejercen este oficio. */
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilTrabajador::class);
    }
}