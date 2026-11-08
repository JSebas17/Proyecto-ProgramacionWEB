<?php

namespace Database\Seeders;

use App\Models\Distrito;
use App\Models\PerfilCliente;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea las cuentas base: administrador y un cliente de prueba.
 * Contraseña de ambas: "password123" (solo para desarrollo).
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Administrador (verifica identidades y modera)
        User::updateOrCreate(['email' => 'admin@ayni.test'], [
            'name'       => 'Administrador',
            'apellidos'  => 'Ayni',
            'password'   => 'password123', // el cast "hashed" la encripta
            'rol'        => 'admin',
            'verificado' => true,
            'distrito_id' => Distrito::where('nombre', 'Cusco')->value('id'),
        ]);

        // Cliente de prueba con su perfil 1:1
        $cliente = User::updateOrCreate(['email' => 'cliente@ayni.test'], [
            'name'       => 'Ana',
            'apellidos'  => 'Ramos',
            'password'   => 'password123',
            'rol'        => 'cliente',
            'verificado' => true,
            'distrito_id' => Distrito::where('nombre', 'Wanchaq')->value('id'),
        ]);

        PerfilCliente::updateOrCreate(['user_id' => $cliente->id], [
            'preferencias_contacto' => 'WhatsApp',
            'direccion_frecuente'   => 'Wanchaq, Cusco',
        ]);
    }
}
