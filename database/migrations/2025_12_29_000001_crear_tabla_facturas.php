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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');
            $table->string('periodo', 7); // formato YYYY-MM (ej: 2026-01)
            $table->date('fecha_corte'); // cuando se congeló el conteo de asociados
            $table->integer('cantidad_asociados'); // cantidad al momento del conteo
            $table->decimal('precio_unitario', 10, 2); // precio por asociado
            $table->decimal('monto_total', 10, 2); // total de la factura
            $table->date('fecha_vencimiento'); // cuando vence el pago
            $table->enum('estado', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('fecha_pago')->nullable(); // cuando se pagó
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('organizacion_id');
            $table->index('periodo');
            $table->index('estado');
            $table->index('fecha_vencimiento');

            // Evitar facturas duplicadas para el mismo periodo
            $table->unique(['organizacion_id', 'periodo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
