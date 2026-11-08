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
 * Perfil 1:1 del usuario con rol trabajador.
 */
    public function up(): void
    {
        Schema::create('perfiles_trabajador', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->foreignId('oficio_id')->constrained('oficios');
            $table->text('descripcion')->nullable();
            $table->unsignedTinyInteger('anios_experiencia')->default(0);
            $table->decimal('tarifa_referencial', 8, 2)->nullable();
            $table->boolean('disponible')->default(true)->index();
            $table->string('foto')->nullable(); // ruta dentro de storage/app/public/uploads
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfil_trabajadors');
    }
};
