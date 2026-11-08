<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega a users los campos propios de Ayni:
     * apellidos, DNI, teléfono, rol, verificación y distrito.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellidos', 100)->nullable()->after('name');
            $table->string('dni', 8)->nullable()->unique()->after('apellidos');
            $table->string('telefono', 15)->nullable()->after('email');
            $table->enum('rol', ['admin', 'trabajador', 'cliente'])->default('cliente')->index();
            $table->boolean('verificado')->default(false)->index();
            $table->boolean('activo')->default(true); // borrado lógico (desactivar cuenta)
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->nullOnDelete();
        });
    }

    /** Revierte los campos agregados. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('distrito_id');
            $table->dropColumn(['apellidos', 'dni', 'telefono', 'rol', 'verificado', 'activo']);
        });
    }
};