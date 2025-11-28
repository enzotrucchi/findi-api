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
        Schema::create('organizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->date('fecha_alta');
            $table->boolean('es_prueba')->default(false);
            $table->date('fecha_fin_prueba')->nullable();
            $table->timestamps();

            $table->index('es_prueba');
            $table->index('fecha_alta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizaciones');
    }
};
