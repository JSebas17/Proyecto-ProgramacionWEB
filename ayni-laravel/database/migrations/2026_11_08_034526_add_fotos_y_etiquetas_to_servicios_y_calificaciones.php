<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega las fotos de trabajos a los servicios (lista JSON de rutas)
     * y las etiquetas rápidas (chips) a las calificaciones.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->json('fotos')->nullable()->after('precio_desde');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->json('etiquetas')->nullable()->after('comentario');
        });
    }

    /** Revierte las columnas agregadas. */
    public function down(): void
    {
        Schema::table('servicios', fn (Blueprint $table) => $table->dropColumn('fotos'));
        Schema::table('calificaciones', fn (Blueprint $table) => $table->dropColumn('etiquetas'));
    }
};