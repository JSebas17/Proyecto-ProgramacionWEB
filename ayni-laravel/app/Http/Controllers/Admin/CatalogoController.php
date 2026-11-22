<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distrito;
use App\Models\Oficio;
use App\Support\Auditoria;
use Illuminate\Http\Request;

/**
 * Gestión de catálogos configurables: oficios y distritos.
 * Permite ampliar la cobertura sin tocar código (escalabilidad).
 */
class CatalogoController extends Controller
{
    /** Muestra ambos catálogos con el uso que tiene cada elemento. */
    public function index()
    {
        return view('admin.catalogos', [
            'oficios'   => Oficio::withCount('perfiles')->orderBy('nombre')->get(),
            'distritos' => Distrito::withCount('usuarios')->orderBy('nombre')->get(),
        ]);
    }

    /** Agrega un oficio. */
    public function storeOficio(Request $request)
    {
        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'max:80', 'unique:oficios,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], ['nombre.unique' => 'Ese oficio ya existe.']);

        $nuevo = Oficio::create($datos);
        Auditoria::registrar('admin.oficio_agregado', ['oficio_id' => $nuevo->id]);
        return back()->with('mensaje', 'Oficio agregado.');
    }

    /** Elimina un oficio solo si ningún trabajador lo usa (integridad referencial). */
    public function destroyOficio(Oficio $oficio)
    {
        if ($oficio->perfiles()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay trabajadores con este oficio.');
        }
        $oficio->delete();
        Auditoria::registrar('admin.oficio_eliminado', ['oficio_id' => $oficio->id]);
        return back()->with('mensaje', 'Oficio eliminado.');
    }

    /** Agrega un distrito. */
    public function storeDistrito(Request $request)
    {
        $datos = $request->validate([
            'nombre'    => ['required', 'string', 'max:80', 'unique:distritos,nombre'],
            'provincia' => ['required', 'string', 'max:80'],
        ], ['nombre.unique' => 'Ese distrito ya existe.']);

        $nuevo = Distrito::create($datos);
        Auditoria::registrar('admin.distrito_agregado', ['distrito_id' => $nuevo->id]);
        return back()->with('mensaje', 'Distrito agregado.');
    }

    /** Elimina un distrito solo si no tiene usuarios ni servicios. */
    public function destroyDistrito(Distrito $distrito)
    {
        if ($distrito->usuarios()->exists() || $distrito->servicios()->exists()) {
            return back()->with('error', 'No se puede eliminar: el distrito está en uso.');
        }
        $distrito->delete();
        Auditoria::registrar('admin.distrito_eliminado', ['distrito_id' => $distrito->id]);
        return back()->with('mensaje', 'Distrito eliminado.');
    }
}