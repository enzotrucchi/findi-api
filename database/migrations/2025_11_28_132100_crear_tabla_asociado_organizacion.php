<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asociado_organizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asociado_id')->constrained('asociados')->onDelete('cascade');
            $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('es_admin')->default(false);
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['asociado_id', 'organizacion_id']);

            // Índices adicionales
            $table->index('activo');
            $table->index(['organizacion_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asociado_organizacion');
    }
};
