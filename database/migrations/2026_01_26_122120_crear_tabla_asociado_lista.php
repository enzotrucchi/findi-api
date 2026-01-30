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
        Schema::create('asociado_lista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asociado_id')->constrained('asociados')->onDelete('cascade');
            $table->foreignId('lista_id')->constrained('listas')->onDelete('cascade');
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['asociado_id', 'lista_id']);

            // Índices adicionales
            $table->index('lista_id');
            $table->index('asociado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asociado_lista');
    }
};
