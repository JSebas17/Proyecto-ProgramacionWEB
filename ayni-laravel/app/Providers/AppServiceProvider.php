<?php

namespace App\Providers;

use App\Support\Auditoria;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // A07 (fallas de autenticación): máximo 5 intentos por minuto para el MISMO correo desde la
        // misma IP (frena adivinar una contraseña) y 20 por minuto por IP (frena probar muchos correos).
        // Antes solo había un límite por IP, que no protegía a una cuenta concreta.
        RateLimiter::for('login', function (Request $request) {
            $bloqueo = function (Request $r, array $cabeceras) {
                Auditoria::registrar('login.bloqueado', ['email' => Auditoria::enmascarar($r->input('email'))]);

                return response()->view('errors.429', [], 429, $cabeceras);
            };

            $correo = is_string($e = $request->input('email')) ? mb_strtolower($e) : '';

            return [
                Limit::perMinute(5)->by($correo . '|' . $request->ip())->response($bloqueo),
                Limit::perMinute(20)->by($request->ip())->response($bloqueo),
            ];
        });

        // A06 (diseño inseguro): frena la creación masiva de cuentas falsas.
        RateLimiter::for('registro', fn (Request $request) => Limit::perHour(15)->by($request->ip()));
    }
}
