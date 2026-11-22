<?php

namespace App\Http\Middleware;

use App\Support\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A01 (pérdida de control de acceso).
 *
 * Antes, desactivar una cuenta solo impedía el próximo login: quien ya tenía la sesión abierta
 * (o la cookie "recordarme") seguía usando la app. Ahora se revisa en cada petición y, si la
 * cuenta fue desactivada, se cierra la sesión al instante.
 */
class CuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->activo) {
            Auditoria::registrar('sesion.cerrada_cuenta_inactiva', ['usuario_id' => $usuario->id]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tu cuenta está desactivada. Si crees que es un error, contacta al administrador.');
        }

        return $next($request);
    }
}
