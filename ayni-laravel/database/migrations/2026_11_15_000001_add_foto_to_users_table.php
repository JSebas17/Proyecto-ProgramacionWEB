<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto de perfil del usuario (cliente, trabajador o admin).
     * Guarda la ruta dentro de storage/app/public, por ejemplo "uploads/perfiles/abc.jpg".
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('telefono');
        });
    }

    /** Quita la columna. */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
