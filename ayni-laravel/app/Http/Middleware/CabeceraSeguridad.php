<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A02 (configuración incorrecta de seguridad) y A04 (fallas criptográficas).
 *
 * Agrega cabeceras de seguridad a TODAS las respuestas web:
 *  - CSP: el navegador solo ejecuta scripts de la propia app y de cdn.jsdelivr.net (Bootstrap).
 *  - X-Frame-Options / frame-ancestors: nadie puede meter Ayni en un iframe (clickjacking).
 *  - X-Content-Type-Options: el navegador no "adivina" el tipo de archivo.
 *  - HSTS: solo si la visita ya llegó por HTTPS (obliga al navegador a seguir usando HTTPS).
 *
 * En modo debug la CSP va como "Report-Only": avisa en la consola del navegador pero no bloquea,
 * porque la página de errores de Laravel usa scripts propios que una CSP estricta rompería.
 */
class CabeceraSeguridad
{
    private const CSP = "default-src 'self'; "
        . "script-src 'self' https://cdn.jsdelivr.net; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "font-src 'self' https://cdn.jsdelivr.net data:; "
        . "img-src 'self' data: blob:; "
        . "connect-src 'self'; "
        . "object-src 'none'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'none'";

    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);
        $h = $respuesta->headers;

        $h->set('X-Frame-Options', 'DENY');
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        // Un PDF no ejecuta scripts; la CSP no le aporta nada y puede romper el visor de PDF del navegador
        if (! str_starts_with((string) $h->get('Content-Type'), 'application/pdf')) {
            $h->set(config('app.debug') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy', self::CSP);
        }

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respuesta;
    }
}
