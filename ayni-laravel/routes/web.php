<?php

use App\Http\Controllers\Admin\CalificacionController as AdminCalificacionController;
use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Admin\PanelController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\TrabajadorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mapa de pantallas (así se conectan entre sí)
|--------------------------------------------------------------------------
| Inicio (buscar) ──> Perfil del trabajador ──> Calificar ──> vuelve al perfil
|        │                    │
|        │                    └─> WhatsApp (contacto directo)
|        ├─> Ofrecer mi servicio: visitante -> Registro | trabajador -> Publicar
|        ├─> Ingresar / Registrarme  ──> vuelven a la página que se intentaba abrir
|        └─> Mi perfil (foto y datos) <─ menú de la cuenta
| Trabajador: Mis servicios <-> Publicar / Editar  ·  Ver mi perfil público
| Administrador: Resumen · Usuarios · Calificaciones · Catálogos
*/

// ---- Públicas ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/ofrecer', [HomeController::class, 'ofrecer'])->name('ofrecer');
Route::get('/trabajadores/{trabajador}', [TrabajadorController::class, 'show'])->name('trabajadores.show');
// CV en PDF: público, con límite para frenar descargas masivas
Route::get('/cv/{usuario}', [CvController::class, 'show'])->middleware('throttle:30,1')->name('cv.descargar');

// ---- Solo visitantes ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    // throttle:login (ver AppServiceProvider): 5 intentos/min por correo+IP y 20/min por IP
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::get('/registro', [AuthController::class, 'registroForm'])->name('registro');
    // throttle:registro: máximo 15 cuentas nuevas por hora desde una misma IP (anti cuentas falsas)
    Route::post('/registro', [AuthController::class, 'registrar'])->middleware('throttle:registro')->name('registro.post');
});

// ---- Usuarios con sesión ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Mi perfil: datos y foto (todos los roles)
    Route::get('/mi-perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/mi-perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::delete('/mi-perfil', [PerfilController::class, 'destroy'])->name('perfil.destroy');

    

    // Calificar a un trabajador (el admin queda bloqueado en el controlador)
    Route::get('/trabajadores/{trabajador}/calificar', [CalificacionController::class, 'create'])->name('calificaciones.create');
    // throttle: máximo 10 calificaciones por minuto (frena el envío masivo de reseñas)
    Route::post('/trabajadores/{trabajador}/calificar', [CalificacionController::class, 'store'])
        ->middleware('throttle:10,1')->name('calificaciones.store');
});

// ---- Solo trabajadores: CRUD de sus servicios ----
Route::middleware(['auth', 'rol:trabajador'])->group(function () {
    Route::get('/publicar', [ServicioController::class, 'create'])->name('publicar');
    // throttle: máximo 20 publicaciones por hora (anti spam de servicios)
    Route::post('/servicios', [ServicioController::class, 'store'])->middleware('throttle:20,60')->name('servicios.store');
    Route::get('/mis-servicios', [ServicioController::class, 'index'])->name('servicios.index');
    Route::get('/servicios/{servicio}/editar', [ServicioController::class, 'edit'])->name('servicios.edit');
    Route::put('/servicios/{servicio}', [ServicioController::class, 'update'])->name('servicios.update');
    Route::delete('/servicios/{servicio}', [ServicioController::class, 'destroy'])->name('servicios.destroy');
});

// ---- Solo administrador (grupo independiente) ----
Route::middleware(['auth', 'rol:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('panel');

    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios');
    Route::patch('/usuarios/{usuario}/verificar', [UsuarioController::class, 'verificar'])->name('usuarios.verificar');
    Route::patch('/usuarios/{usuario}/activar', [UsuarioController::class, 'activar'])->name('usuarios.activar');

    Route::get('/calificaciones', [AdminCalificacionController::class, 'index'])->name('calificaciones');
    Route::delete('/calificaciones/{calificacion}', [AdminCalificacionController::class, 'destroy'])->name('calificaciones.destroy');

    Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos');
    Route::post('/catalogos/oficios', [CatalogoController::class, 'storeOficio'])->name('catalogos.oficios.store');
    Route::delete('/catalogos/oficios/{oficio}', [CatalogoController::class, 'destroyOficio'])->name('catalogos.oficios.destroy');
    Route::post('/catalogos/distritos', [CatalogoController::class, 'storeDistrito'])->name('catalogos.distritos.store');
    Route::delete('/catalogos/distritos/{distrito}', [CatalogoController::class, 'destroyDistrito'])->name('catalogos.distritos.destroy');
});
