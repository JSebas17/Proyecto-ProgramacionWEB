<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Ruta (en el disco privado) del CV en PDF del trabajador. Opcional. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cv')->nullable()->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cv');
        });
    }
};
