<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Ejecuta los seeders en orden de dependencias.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DistritoSeeder::class,   // 1. catálogos
            OficioSeeder::class,
            UsuarioSeeder::class,    // 2. admin y cliente (autores de reseñas)
            TrabajadorSeeder::class, // 3. trabajadores + perfiles + servicios + calificaciones
        ]);
    }
}