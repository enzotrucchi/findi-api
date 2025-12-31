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
        Schema::create('facturacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');
            $table->string('periodo', 7); // formato MM/YYYY o YYYY-MM
            $table->integer('cantidad_asociados'); // congelado del día 1
            $table->date('fecha_pago')->nullable(); // cuando se pagó
            $table->timestamps();

            // Índices
            $table->index('organizacion_id');
            $table->index('periodo');
            $table->index('fecha_pago');

            // Evitar registros duplicados para el mismo periodo
            $table->unique(['organizacion_id', 'periodo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturacion');
    }
};
