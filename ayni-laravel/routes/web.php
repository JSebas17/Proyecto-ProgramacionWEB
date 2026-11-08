<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\TrabajadorController;
use Illuminate\Support\Facades\Route;

// ---- Públicas ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/trabajadores/{trabajador}', [TrabajadorController::class, 'show'])->name('trabajadores.show');

// ---- Solo visitantes ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/registro', [AuthController::class, 'registroForm'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registrar'])->name('registro.post');
});

// ---- Usuarios con sesión ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Calificar a un trabajador (cliente o trabajador; el admin queda bloqueado en el controlador)
    Route::get('/trabajadores/{trabajador}/calificar', [CalificacionController::class, 'create'])->name('calificaciones.create');
    Route::post('/trabajadores/{trabajador}/calificar', [CalificacionController::class, 'store'])->name('calificaciones.store');
});

// ---- Solo trabajadores: CRUD de sus servicios ----
Route::middleware(['auth', 'rol:trabajador'])->group(function () {
    Route::get('/publicar', [ServicioController::class, 'create'])->name('publicar');
    Route::post('/servicios', [ServicioController::class, 'store'])->name('servicios.store');
    Route::get('/mis-servicios', [ServicioController::class, 'index'])->name('servicios.index');
    Route::get('/servicios/{servicio}/editar', [ServicioController::class, 'edit'])->name('servicios.edit');
    Route::put('/servicios/{servicio}', [ServicioController::class, 'update'])->name('servicios.update');
    Route::delete('/servicios/{servicio}', [ServicioController::class, 'destroy'])->name('servicios.destroy');
});