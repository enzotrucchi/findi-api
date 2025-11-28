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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora');
            $table->text('detalle');
            $table->decimal('monto', 10, 2);
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->string('adjunto', 500)->nullable();

            // Foreign keys
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->onDelete('cascade');
            $table->foreignId('asociado_id')->nullable()->constrained('asociados')->onDelete('set null');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->foreignId('modo_pago_id')->nullable()->constrained('modos_pago')->onDelete('set null');
            $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');

            $table->timestamps();

            // Índices
            $table->index('fecha');
            $table->index('tipo');
            $table->index('status');
            $table->index(['organizacion_id', 'fecha']);
            $table->index(['proyecto_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
