<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\PerfilCliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Autenticación: registro, inicio y cierre de sesión.
 * Reemplaza a registro.php, login.php y logout.php del prototipo.
 */
class AuthController extends Controller
{
    /** Muestra el formulario de login. */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Valida credenciales e inicia sesión.
     * Auth::attempt compara la contraseña contra el hash guardado.
     */
    public function login(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'clave' => ['required'],
        ], [
            'email.required' => 'Escribe tu correo.',
            'email.email'    => 'Escribe un correo válido.',
            'clave.required' => 'Escribe tu contraseña.',
        ]);

        $credenciales = [
            'email'    => strtolower($datos['email']),
            'password' => $datos['clave'],
            'activo'   => true, // las cuentas desactivadas no pueden entrar
        ];

        if (Auth::attempt($credenciales, $request->boolean('recordar'))) {
    // Regenera el ID de sesión (evita fijación de sesión)
    $request->session()->regenerate();

    // Cada rol aterriza en su sección; evita rutas a las que no tiene acceso
    $destino = match (Auth::user()->rol) {
        'admin'      => route('admin.panel'),
        'trabajador' => route('servicios.index'),
        default      => route('home'),
    };

    return redirect()->to($destino);
}

        return back()
            ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
            ->onlyInput('email');
    }

    /** Muestra el formulario de registro con el catálogo de distritos. */
    public function registroForm()
    {
        return view('auth.registro', ['distritos' => Distrito::orderBy('nombre')->get()]);
    }

    /**
     * Crea la cuenta, inicia sesión y redirige según el rol.
     * Los mensajes de error son los mismos del prototipo.
     */
    public function registrar(Request $request)
    {
        $datos = $request->validate([
            'rol'         => ['required', 'in:cliente,trabajador'],
            'nombre'      => ['required', 'string', 'max:100'],
            'apellidos'   => ['required', 'string', 'max:100'],
            'dni'         => ['required', 'digits:8', 'unique:users,dni'],
            'telefono'    => ['required', 'regex:/^9[0-9]{8}$/'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'distrito_id' => ['required', 'exists:distritos,id'],
            'clave'       => ['required', 'min:8', 'same:clave2'],
            'clave2'      => ['required'],
        ], [
            'nombre.required'      => 'Escribe tu nombre.',
            'apellidos.required'   => 'Escribe tus apellidos.',
            'dni.digits'           => 'El DNI tiene 8 números.',
            'dni.required'         => 'El DNI tiene 8 números.',
            'dni.unique'           => 'Ese DNI ya está registrado.',
            'telefono.required'    => 'Escribe 9 números, empezando con 9.',
            'telefono.regex'       => 'Escribe 9 números, empezando con 9.',
            'email.required'       => 'Escribe un correo válido.',
            'email.email'          => 'Escribe un correo válido.',
            'email.unique'         => 'Ese correo ya está registrado.',
            'distrito_id.required' => 'Elige tu distrito.',
            'distrito_id.exists'   => 'Elige tu distrito.',
            'clave.required'       => 'Mínimo 8 caracteres.',
            'clave.min'            => 'Mínimo 8 caracteres.',
            'clave.same'           => 'Las contraseñas no coinciden.',
            'clave2.required'      => 'Repite la contraseña.',
        ]);

        // El rol sale de una lista cerrada ("in:"), así nadie puede registrarse como admin
        $usuario = User::create([
            'name'        => $datos['nombre'],
            'apellidos'   => $datos['apellidos'],
            'dni'         => $datos['dni'],
            'telefono'    => $datos['telefono'],
            'email'       => strtolower($datos['email']),
            'distrito_id' => $datos['distrito_id'],
            'rol'         => $datos['rol'],
            'password'    => $datos['clave'], // el cast "hashed" la encripta
        ]);

        // Los clientes tienen su perfil 1:1 desde el inicio
        if ($usuario->rol === 'cliente') {
            PerfilCliente::create(['user_id' => $usuario->id]);
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        // Trabajador: a completar su servicio. Cliente: a buscar.
        return $usuario->rol === 'trabajador'
            ? redirect()->route('publicar')->with('mensaje', '¡Cuenta creada! Completa tu servicio.')
            : redirect()->route('home')->with('mensaje', '¡Cuenta creada! Ya puedes buscar trabajo.');
    }

    /** Cierra la sesión (por POST con CSRF) e invalida el token. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}