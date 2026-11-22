<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Procesa las imágenes que suben los usuarios antes de guardarlas.
 *
 * Por qué se re-codifican y no se guardan tal cual:
 *  - Peso: una foto de celular pesa 3-8 MB; aquí queda en ~40-150 KB (bajo consumo de datos).
 *  - Seguridad: al volver a dibujar la imagen se descarta cualquier contenido oculto en el archivo.
 *  - Orientación: las fotos de celular traen un giro en los metadatos EXIF; se corrige aquí.
 *
 * Si la extensión GD no está activa en el servidor, se guarda el archivo original (no se rompe nada).
 */
class Imagenes
{
    private const DISCO = 'public';

    /** Tope de megapíxeles: una imagen enorme (o una "bomba de descompresión") agotaría la memoria del servidor. */
    private const MAX_PIXELES = 40_000_000;

    /** Foto de perfil: recorte cuadrado centrado de hasta 400 x 400 px. Devuelve la ruta guardada. */
    public static function avatar(UploadedFile $archivo): string
    {
        return self::guardar($archivo, 'uploads/perfiles', 400, cuadrada: true, campo: 'foto');
    }

    /** Foto de un trabajo realizado: lado mayor de hasta 1280 px. Devuelve la ruta guardada. */
    public static function trabajo(UploadedFile $archivo): string
    {
        return self::guardar($archivo, 'uploads/servicios', 1280, cuadrada: false, campo: 'fotos');
    }

    /** Borra del disco una o varias rutas (ignora vacías). */
    public static function borrar(string|array|null $rutas): void
    {
        $rutas = array_filter((array) $rutas);
        if ($rutas) {
            Storage::disk(self::DISCO)->delete($rutas);
        }
    }

    // ---------------------------------------------------------------

    /**
     * OWASP A10 (manejo inadecuado de condiciones excepcionales): ante una imagen dañada, gigante o
     * un disco que falla, la app "falla cerrado": rechaza con un mensaje claro y no guarda nada.
     * Antes, una imagen que GD no podía leer se guardaba tal cual (sin re-codificar).
     */
    private static function guardar(UploadedFile $archivo, string $carpeta, int $max, bool $cuadrada, string $campo): string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $archivo->store($carpeta, self::DISCO); // sin GD: se guarda el original (ya validado por mimes)
        }

        $origenRuta = $archivo->getRealPath();
        $info = $origenRuta ? @getimagesize($origenRuta) : false;

        if ($info === false) {
            self::rechazar($campo, 'No pudimos leer la imagen. Prueba con otra foto.');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELES) {
            self::rechazar($campo, 'La imagen es demasiado grande (máximo 40 megapíxeles). Redúcela e inténtalo de nuevo.');
        }

        $contenido = @file_get_contents($origenRuta);
        $origen    = $contenido === false ? false : @imagecreatefromstring($contenido);
        unset($contenido);

        if ($origen === false) {
            self::rechazar($campo, 'La imagen está dañada o no es válida. Prueba con otra foto.');
        }

        $origen = self::corregirOrientacion($origen, $archivo);
        $ancho  = imagesx($origen);
        $alto   = imagesy($origen);

        if ($cuadrada) {
            $lado    = min($ancho, $alto);
            $destino = min($max, $lado);
            $x = intdiv($ancho - $lado, 2);
            $y = intdiv($alto - $lado, 2);
            $lienzo = imagecreatetruecolor($destino, $destino);
            self::fondoBlanco($lienzo);
            imagecopyresampled($lienzo, $origen, 0, 0, $x, $y, $destino, $destino, $lado, $lado);
        } else {
            $escala = min(1, $max / max($ancho, $alto));
            $nuevoAncho = max(1, (int) round($ancho * $escala));
            $nuevoAlto  = max(1, (int) round($alto * $escala));
            $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            self::fondoBlanco($lienzo);
            imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        }

        // El buffer se cierra SIEMPRE (finally), aunque imagejpeg falle a medias
        ob_start();
        try {
            $codificada = imagejpeg($lienzo, null, 82);
        } finally {
            $binario = ob_get_clean();
        }

        if (! $codificada || $binario === false || $binario === '') {
            self::rechazar($campo, 'No pudimos procesar la imagen. Prueba con otra foto.');
        }

        $rutaFinal = $carpeta . '/' . Str::random(40) . '.jpg';

        if (! Storage::disk(self::DISCO)->put($rutaFinal, $binario)) {
            throw new RuntimeException('No se pudo guardar la imagen en el disco.');
        }

        return $rutaFinal;
    }

    /** Corta el proceso con un error de validación mostrado junto al campo de la foto. */
    private static function rechazar(string $campo, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }

    /** Las imágenes PNG/WEBP con transparencia se pintan sobre blanco (JPEG no tiene transparencia). */
    private static function fondoBlanco(\GdImage $lienzo): void
    {
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));
    }

    /** Gira la imagen según el dato EXIF de los JPEG tomados con celular. */
    private static function corregirOrientacion(\GdImage $imagen, UploadedFile $archivo): \GdImage
    {
        if (! function_exists('exif_read_data') || $archivo->getMimeType() !== 'image/jpeg') {
            return $imagen;
        }

        $exif = @exif_read_data($archivo->getRealPath());
        $giro = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($giro !== 0) {
            $girada = imagerotate($imagen, $giro, 0);
            if ($girada !== false) {
                return $girada;
            }
        }

        return $imagen;
    }
}
