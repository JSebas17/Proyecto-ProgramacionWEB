<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OWASP A09 (fallas en registro y monitoreo).
 *
 * Deja un rastro de los eventos de seguridad en storage/logs/seguridad-AAAA-MM-DD.log
 * (se conservan 90 días): inicios de sesión, intentos fallidos, bloqueos, accesos denegados
 * y acciones del administrador. Nunca se escriben contraseñas; los correos van enmascarados.
 *
 * Revisar: php artisan pail --filter=seguridad   (o abrir el archivo del día).
 */
class Auditoria
{
    /** Registra un evento. Un fallo al escribir el log NUNCA debe romper la petición del usuario. */
    public static function registrar(string $evento, array $datos = []): void
    {
        try {
            $r = request();

            Log::channel('seguridad')->info($evento, array_merge([
                'ip'         => $r->ip(),
                'usuario_id' => auth()->id(),
                'ruta'       => $r->method() . ' /' . ltrim($r->path(), '/'),
                'agente'     => mb_substr((string) $r->userAgent(), 0, 120),
            ], $datos));
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** "lucia@test.pe" -> "lu***@test.pe" (el log sirve para investigar sin exponer el correo completo). */
    public static function enmascarar(mixed $email): string
    {
        if (! is_string($email) || ! str_contains($email, '@')) {
            return '(inválido)';
        }

        [$usuario, $dominio] = explode('@', mb_strtolower($email), 2);

        return mb_substr($usuario, 0, 2) . '***@' . mb_substr($dominio, 0, 80);
    }
}
