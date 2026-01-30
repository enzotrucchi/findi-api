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
        Schema::create('listas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');
            $table->string('color', 7)->nullable();
            $table->timestamps();

            // Índice único para evitar nombres duplicados por organización
            $table->unique(['organizacion_id', 'nombre']);

            // Índices adicionales
            $table->index('organizacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listas');
    }
};
