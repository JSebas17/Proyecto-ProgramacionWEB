<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Guarda los documentos privados (CV en PDF).
 *
 * A diferencia de las fotos, los CV NO van al disco "public": quedan en storage/app/private/cvs,
 * donde ninguna URL puede llegar. Solo se entregan por CvController, que revisa quién pide el archivo.
 */
class Documentos
{
    private const DISCO = 'local';

    /** Guarda el CV con un nombre aleatorio. Devuelve la ruta relativa al disco. */
    public static function guardarCv(UploadedFile $archivo): string
    {
        // Además de la validación "mimes:pdf", se comprueba la firma real del archivo: un PDF empieza con "%PDF-"
        $ruta     = $archivo->getRealPath();
        $cabecera = $ruta ? @file_get_contents($ruta, false, null, 0, 5) : false;

        if ($cabecera !== '%PDF-') {
            throw ValidationException::withMessages(['cv' => 'El archivo no es un PDF válido.']);
        }

        $guardado = Storage::disk(self::DISCO)->putFileAs('cvs', $archivo, Str::random(40) . '.pdf');

        if (! $guardado) {
            throw new RuntimeException('No se pudo guardar el CV en el disco.');
        }

        return $guardado;
    }

    /** Borra uno o varios CV (ignora vacíos). */
    public static function borrar(string|array|null $rutas): void
    {
        $rutas = array_filter((array) $rutas);
        if ($rutas) {
            Storage::disk(self::DISCO)->delete($rutas);
        }
    }

    public static function existe(?string $ruta): bool
    {
        return $ruta && Storage::disk(self::DISCO)->exists($ruta);
    }
}
