<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyecto_historial', function (Blueprint $table) {
            $table->id();

            // FK al proyecto
            $table->foreignId('proyecto_id')
                ->constrained('proyectos')
                ->cascadeOnDelete();

            // FK al asociado (ajusta el nombre de tabla si es distinto)
            $table->foreignId('asociado_id')
                ->constrained('asociados')
                ->cascadeOnDelete();

            $table->text('detalle');

            // Fecha "funcional" del comentario
            $table->timestamp('fecha')->useCurrent();

            // Timestamps estándar de Laravel (por si querés usarlos también)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_historial');
    }
};
