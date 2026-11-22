<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\PerfilCliente;
use App\Models\User;
use App\Support\Auditoria;
use App\Support\Documentos;
use App\Support\Imagenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Autenticación: registro (con foto de perfil opcional), inicio y cierre de sesión.
 */
class AuthController extends Controller
{
    /** Contraseñas que se rechazan aunque cumplan el largo (las primeras de cualquier diccionario). */
    private const CLAVES_COMUNES = [
        'password1', 'password123', 'contrasena1', 'contrasena123', 'qwerty123', 'abc12345',
        'admin123', 'ayni1234', 'ayni2026', 'iloveyou1', '12345678a', 'a1234567',
    ];

    /** Muestra el formulario de login. */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Valida credenciales e inicia sesión.
     * Auth::attempt compara la contraseña contra el hash guardado.
     * La ruta tiene "throttle" (ver routes/web.php): frena los intentos de adivinar contraseñas.
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
            Auditoria::registrar('login.exitoso', ['rol' => Auth::user()->rol]);

            // Cada rol aterriza en su sección por defecto...
            $destino = match (Auth::user()->rol) {
                'admin'      => route('admin.panel'),
                'trabajador' => route('servicios.index'),
                default      => route('home'),
            };

            // ...pero si el usuario venía de otra página (p. ej. "Calificar"), vuelve ahí.
            return redirect()->intended($destino);
        }

        Auditoria::registrar('login.fallido', ['email' => Auditoria::enmascarar($datos['email'])]);

        return back()
            ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
            ->onlyInput('email');
    }

    /** Muestra el formulario de registro. ?rol=trabajador deja marcada la opción "Ofrezco mi servicio". */
    public function registroForm(Request $request)
    {
        return view('auth.registro', [
            'distritos' => Distrito::catalogo(),
            'rolInicial' => $request->query('rol') === 'trabajador' ? 'trabajador' : 'cliente',
        ]);
    }

    /**
     * Crea la cuenta, inicia sesión y redirige según el rol.
     * La foto de perfil es opcional; se recorta y comprime en App\Support\Imagenes.
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
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'], // KB
            'cv'          => ['nullable', 'file', 'mimes:pdf', 'max:5120'], // KB; solo se guarda si es trabajador
            // A07: mínimo 8, máximo 72 (bcrypt ignora lo que pase de 72 bytes), con letra y número,
            // y fuera de la lista de contraseñas comunes
            'clave'       => ['bail', 'required', 'string', 'min:8', 'max:72', 'regex:/[A-Za-z]/', 'regex:/[0-9]/',
                function (string $atributo, mixed $valor, \Closure $falla) {
                    if (in_array(mb_strtolower((string) $valor), self::CLAVES_COMUNES, true)) {
                        $falla('Esa contraseña es muy común. Elige otra.');
                    }
                },
                'same:clave2'],
            'clave2'      => ['required'],
            'acepto'      => ['accepted'], // consentimiento explícito (sección 5.5 del informe)
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
            'foto.image'           => 'El archivo debe ser una imagen.',
            'foto.mimes'           => 'Solo JPG, PNG o WEBP.',
            'foto.max'             => 'La foto pesa máximo 3 MB.',
            'cv.mimes'             => 'El CV debe ser un archivo PDF.',
            'cv.max'               => 'El CV pesa máximo 5 MB.',
            'clave.required'       => 'Mínimo 8 caracteres.',
            'clave.min'            => 'Mínimo 8 caracteres.',
            'clave.max'            => 'La contraseña admite máximo 72 caracteres.',
            'clave.regex'          => 'Usa al menos una letra y un número.',
            'clave.same'           => 'Las contraseñas no coinciden.',
            'clave2.required'      => 'Repite la contraseña.',
            'acepto.accepted'      => 'Debes aceptar el uso de tus datos para crear la cuenta.',
        ]);

        $usuario = DB::transaction(function () use ($request, $datos) {
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
                'foto'        => $request->hasFile('foto') ? Imagenes::avatar($request->file('foto')) : null,
                // El CV es solo para trabajadores (privado: ver App\Support\Documentos)
                'cv'          => $datos['rol'] === 'trabajador' && $request->hasFile('cv') ? Documentos::guardarCv($request->file('cv')) : null,
            ]);

            // Los clientes tienen su perfil 1:1 desde el inicio
            if ($usuario->rol === 'cliente') {
                PerfilCliente::create(['user_id' => $usuario->id]);
            }

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();
        Auditoria::registrar('registro.creado', ['usuario_id' => $usuario->id, 'rol' => $usuario->rol]);

        // Trabajador: a completar su servicio. Cliente: a buscar.
        return $usuario->rol === 'trabajador'
            ? redirect()->route('publicar')->with('mensaje', '¡Cuenta creada! Ahora publica tu primer servicio.')
            : redirect()->route('home')->with('mensaje', '¡Cuenta creada! Ya puedes buscar trabajo.');
    }

    /** Cierra la sesión (por POST con CSRF) e invalida el token. */
    public function logout(Request $request)
    {
        Auditoria::registrar('logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
