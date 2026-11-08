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
 * Servicios que publica un trabajador.
 */
public function up(): void
{
    Schema::create('servicios', function (Blueprint $table) {
        $table->id();
        $table->foreignId('trabajador_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('distrito_id')->constrained('distritos');
        $table->string('titulo', 120);
        $table->text('descripcion');
        $table->decimal('precio_desde', 8, 2)->nullable();
        $table->enum('estado', ['activo', 'inactivo'])->default('activo')->index();
        $table->timestamps();
        // Índice compuesto para acelerar la búsqueda por distrito y estado
        $table->index(['distrito_id', 'estado']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
