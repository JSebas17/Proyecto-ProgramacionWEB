<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Gestión de usuarios: verificar identidad y activar/desactivar cuentas.
 */
class UsuarioController extends Controller
{
    /** Lista usuarios; ?estado=pendientes muestra trabajadores sin verificar. */
    public function index(Request $request)
    {
        $usuarios = User::with(['distrito', 'perfilTrabajador.oficio'])
            ->when($request->query('estado') === 'pendientes',
                fn ($q) => $q->trabajadores()->where('verificado', false))
            ->when($request->query('rol'), fn ($q, $rol) => $q->where('rol', $rol))
            ->orderBy('verificado')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios', compact('usuarios'));
    }

    /** Alterna la verificación de identidad. */
    public function verificar(User $usuario)
    {
        $usuario->update(['verificado' => ! $usuario->verificado]);
        Cache::forget('admin.metricas');

        return back()->with('mensaje', $usuario->verificado
            ? "Identidad de {$usuario->nombre_completo} verificada."
            : "Se quitó la verificación de {$usuario->nombre_completo}.");
    }

    /** Activa o desactiva la cuenta (borrado lógico). El admin no puede desactivarse a sí mismo. */
    public function activar(User $usuario)
    {
        abort_if($usuario->id === auth()->id(), 403, 'No puedes desactivar tu propia cuenta.');

        $usuario->update(['activo' => ! $usuario->activo]);
        Cache::forget('admin.metricas');

        return back()->with('mensaje', $usuario->activo ? 'Cuenta activada.' : 'Cuenta desactivada.');
    }
}