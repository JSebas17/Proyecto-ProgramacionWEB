<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectUsersTo('/');
    // Alias para usar ->middleware('rol:trabajador')
    $middleware->alias(['rol' => \App\Http\Middleware\RolMiddleware::class]);

    // Seguridad en TODAS las rutas web: cabeceras (A02/A04) y cierre de sesión de cuentas desactivadas (A01)
    $middleware->web(append: [
        \App\Http\Middleware\CabeceraSeguridad::class,
        \App\Http\Middleware\CuentaActiva::class,
    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
