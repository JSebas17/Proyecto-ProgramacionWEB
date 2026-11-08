<?php

namespace Database\Seeders;

use App\Models\Calificacion;
use App\Models\Distrito;
use App\Models\Oficio;
use App\Models\PerfilTrabajador;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Migra los trabajadores de ejemplo del prototipo PHP (datos.php).
 * Por cada uno crea: usuario, perfil, un servicio y calificaciones
 * cuyo promedio y cantidad reproducen la nota y reseñas originales.
 */
class TrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        // [nombre, apellidos, oficio, distrito, nota, nº reseñas, precio, verificado, teléfono]
        $trabajadores = [
            ['Rosa',   'Quispe H.',  'Pintura',      'San Sebastián', 4.9, 18, 50,   true,  '51900000001'],
            ['Juan C.', 'Mamani',    'Electricista', 'San Sebastián', 4.8, 23, 60,   true,  '51900000002'],
            ['Marco',  'Paredes',    'Hotelería',    'Santiago',      4.7, 9,  null, true,  '51900000003'],
            ['Luis',   'Condori',    'Gasfitería',   'San Sebastián', 4.6, 14, 40,   true,  '51900000004'],
            ['Elena',  'Huamán',     'Limpieza',     'San Jerónimo',  4.5, 7,  45,   true,  '51900000005'],
            ['Walter', 'Ayma',       'Electricista', 'San Sebastián', 4.4, 11, 55,   true,  '51900000006'],
            ['Pedro',  'Callo',      'Carpintería',  'Wanchaq',       3.9, 5,  70,   false, '51900000007'],
        ];

        // Clientes que firman las calificaciones: el de prueba y el admin
        $autores = User::whereIn('email', ['cliente@ayni.test', 'admin@ayni.test'])->pluck('id')->all();

        foreach ($trabajadores as $i => [$nombre, $apellidos, $oficio, $distrito, $nota, $resenas, $precio, $verificado, $tel]) {
            $distritoId = Distrito::where('nombre', $distrito)->value('id');

            // 1. Usuario con rol trabajador
            $usuario = User::updateOrCreate(['email' => 'trabajador' . ($i + 1) . '@ayni.test'], [
                'name'        => $nombre,
                'apellidos'   => $apellidos,
                'telefono'    => $tel,
                'password'    => 'password123',
                'rol'         => 'trabajador',
                'verificado'  => $verificado,
                'distrito_id' => $distritoId,
            ]);

            // 2. Perfil profesional (1:1)
            PerfilTrabajador::updateOrCreate(['user_id' => $usuario->id], [
                'oficio_id'          => Oficio::where('nombre', $oficio)->value('id'),
                'descripcion'        => $this->descripcion($apellidos, $oficio),
                'anios_experiencia'  => rand(2, 15),
                'tarifa_referencial' => $precio,
                'disponible'         => true,
            ]);

            // 3. Un servicio publicado
            $servicio = Servicio::updateOrCreate(
                ['trabajador_id' => $usuario->id, 'titulo' => "Servicio de $oficio"],
                [
                    'distrito_id'  => $distritoId,
                    'descripcion'  => $this->descripcion($apellidos, $oficio),
                    'precio_desde' => $precio,
                    'estado'       => 'activo',
                ]
            );

            // 4. Calificaciones que reproducen la nota y cantidad originales
            if ($servicio->calificaciones()->count() === 0) {
                foreach ($this->puntuaciones($nota, $resenas) as $n => $puntos) {
                    Calificacion::create([
                        'servicio_id' => $servicio->id,
                        'autor_id'    => $autores[$n % count($autores)],
                        'puntuacion'  => $puntos,
                        'comentario'  => $puntos >= 4 ? 'Buen trabajo, recomendado.' : 'Cumplió, pero puede mejorar.',
                    ]);
                }
            }
        }
    }

    /** Texto de descripción; Mamani conserva el de datos.php. */
    private function descripcion(string $apellidos, string $oficio): string
    {
        if ($apellidos === 'Mamani') {
            return 'Instalaciones eléctricas, tableros y reparación de cortes para viviendas, '
                 . 'tiendas y hostales. Atiendo en toda la ciudad.';
        }
        return "Servicio profesional de $oficio en Cusco.";
    }

    /**
     * Genera $cantidad puntuaciones (1 a 5) cuyo promedio redondeado es $nota.
     * Reparte 5 y 4 (y algún 3) hasta aproximar el promedio objetivo.
     */
    private function puntuaciones(float $nota, int $cantidad): array
    {
        $total = (int) round($nota * $cantidad); // suma objetivo
        $lista = array_fill(0, $cantidad, 4);    // base: todos en 4
        $suma  = 4 * $cantidad;

        // Sube a 5 hasta alcanzar la suma, o baja a 3 si hace falta bajar
        for ($i = 0; $i < $cantidad && $suma < $total; $i++) { $lista[$i] = 5; $suma++; }
        for ($i = 0; $i < $cantidad && $suma > $total; $i++) { $lista[$i] = 3; $suma--; }

        return $lista;
    }
}
