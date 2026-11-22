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
        // A07/A02: en producción NO se crean cuentas con la contraseña pública "password123".
        // El admin solo se crea si defines SEED_ADMIN_PASSWORD (y no se crea el cliente de prueba).
        $produccion = app()->isProduction();
        $claveAdmin = $produccion ? env('SEED_ADMIN_PASSWORD') : 'password123';

        if ($produccion && (! $claveAdmin || strlen($claveAdmin) < 12)) {
            throw new \RuntimeException('Producción: define SEED_ADMIN_PASSWORD (mínimo 12 caracteres) antes de ejecutar los seeders.');
        }

        // Administrador (verifica identidades y modera)
        User::updateOrCreate(['email' => 'admin@ayni.test'], [
            'name'       => 'Administrador',
            'apellidos'  => 'Ayni',
            'password'   => $claveAdmin, // el cast "hashed" la encripta
            'rol'        => 'admin',
            'verificado' => true,
            'distrito_id' => Distrito::where('nombre', 'Cusco')->value('id'),
        ]);

        if ($produccion) {
            return;
        }

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
