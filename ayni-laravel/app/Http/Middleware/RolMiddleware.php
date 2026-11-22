<?php

namespace App\Http\Middleware;

use App\Support\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta a ciertos roles. Uso: ->middleware('rol:trabajador,admin')
 */
class RolMiddleware
{
    /**
     * @param  string  ...$roles  Roles permitidos para entrar.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! in_array($request->user()?->rol, $roles, true)) {
            Auditoria::registrar('acceso.denegado', ['rol' => $request->user()?->rol, 'requeridos' => implode(',', $roles)]);

            return redirect()->route('home')
                ->with('error', 'No tienes permiso para entrar a esa sección.');
        }

        return $next($request);
    }
}