<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices para las consultas más repetidas de la app (fase 7: optimización).
     * Cada índice tiene nombre propio para poder quitarlo exacto en down().
     *
     * - users (rol, activo, verificado): el buscador siempre filtra trabajadores activos y verificados.
     * - servicios (trabajador_id, estado): perfil público y calificar piden "servicios activos de X".
     * - calificaciones (autor_id, servicio_id): comprueba si un usuario ya calificó un servicio.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['rol', 'activo', 'verificado'], 'users_rol_activo_verificado_idx');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->index(['trabajador_id', 'estado'], 'servicios_trabajador_estado_idx');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->index(['autor_id', 'servicio_id'], 'calificaciones_autor_servicio_idx');
        });
    }

    /** Quita los tres índices. */
    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropIndex('users_rol_activo_verificado_idx'));
        Schema::table('servicios', fn (Blueprint $t) => $t->dropIndex('servicios_trabajador_estado_idx'));
        Schema::table('calificaciones', fn (Blueprint $t) => $t->dropIndex('calificaciones_autor_servicio_idx'));
    }
};
