<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/** Distrito de cobertura (catálogo configurable). */
class Distrito extends Model
{
    protected $fillable = ['nombre', 'provincia'];

    /** Clave de caché del listado (el catálogo casi no cambia: se guarda 1 hora). */
    public const CACHE_KEY = 'catalogo.distritos.v2';

    /** Si el admin agrega o elimina un distrito, el listado guardado se descarta solo. */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Todos los distritos por nombre, leídos del caché (evita una consulta en cada página).
     * Se guardan como arreglos simples: Laravel 13 no deserializa modelos desde el caché
     * ('serializable_classes' => false) y volverían como objetos rotos. Al leer se
     * reconstruyen como modelos con hydrate(), así las vistas no cambian.
     */
    public static function catalogo()
    {
        $filas = Cache::remember(self::CACHE_KEY, 3600, fn () => static::orderBy('nombre')->get()->toArray());

        return static::hydrate($filas);
    }

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