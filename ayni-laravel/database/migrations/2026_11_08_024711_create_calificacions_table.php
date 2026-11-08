<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
 * Calificaciones que dejan los usuarios tras un servicio.
 */
public function up(): void
{
    Schema::create('calificaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
        $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
        $table->unsignedTinyInteger('puntuacion'); // 1 a 5
        $table->string('comentario', 500)->nullable();
        $table->timestamps();
        $table->index(['servicio_id', 'created_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacions');
    }
};
