<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/** Oficio (electricista, pintor, gasfitero...). */
class Oficio extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    /** Claves de caché: orden alfabético (formularios) y orden del catálogo (inicio). */
    public const CACHE_KEYS = ['catalogo.oficios.v2.nombre', 'catalogo.oficios.v2.id'];

    /** Si el admin agrega o elimina un oficio, los listados guardados se descartan solos. */
    protected static function booted(): void
    {
        $limpiar = fn () => array_map(fn ($k) => Cache::forget($k), self::CACHE_KEYS);
        static::saved($limpiar);
        static::deleted($limpiar);
    }

    /**
     * Todos los oficios, leídos del caché por 1 hora.
     * $orden = 'nombre' (para los <select>) o 'id' (orden del catálogo, como en el wireframe).
     */
    public static function catalogo(string $orden = 'nombre')
    {
        $orden = $orden === 'id' ? 'id' : 'nombre';

        // Arreglos simples en caché (Laravel 13 no deserializa modelos); se reconstruyen al leer.
        $filas = Cache::remember('catalogo.oficios.v2.' . $orden, 3600, fn () => static::orderBy($orden)->get()->toArray());

        return static::hydrate($filas);
    }

    /** Perfiles de trabajadores que ejercen este oficio. */
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilTrabajador::class);
    }
}