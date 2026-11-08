<?php

namespace Database\Seeders;

use App\Models\Distrito;
use Illuminate\Database\Seeder;

/**
 * Carga el catálogo de distritos con cobertura de la plataforma.
 */
class DistritoSeeder extends Seeder
{
    /** Inserta los distritos (updateOrCreate evita duplicados al re-ejecutar). */
    public function run(): void
    {
        $distritos = ['Cusco', 'Santiago', 'San Sebastián', 'San Jerónimo', 'Wanchaq'];

        foreach ($distritos as $nombre) {
            Distrito::updateOrCreate(['nombre' => $nombre], ['provincia' => 'Cusco']);
        }
    }
}
