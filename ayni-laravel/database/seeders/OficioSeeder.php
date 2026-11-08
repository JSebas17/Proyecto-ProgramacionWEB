<?php

namespace Database\Seeders;

use App\Models\Oficio;
use Illuminate\Database\Seeder;

/**
 * Carga el catálogo de oficios disponibles (los mismos del buscador).
 */
class OficioSeeder extends Seeder
{
    /** Inserta los oficios con una descripción corta. */
    public function run(): void
    {
        $oficios = [
            'Electricista' => 'Instalaciones y reparaciones eléctricas',
            'Pintura'      => 'Pintura y acabados para el hogar',
            'Gasfitería'   => 'Instalación y reparación de agua y desagüe',
            'Carpintería'  => 'Muebles y trabajos en madera',
            'Hotelería'    => 'Administración y atención hotelera',
            'Limpieza'     => 'Limpieza de hogares y negocios',
        ];

        foreach ($oficios as $nombre => $descripcion) {
            Oficio::updateOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }
    }
}