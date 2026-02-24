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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_pago_id')->constrained('planes_pago')->onDelete('cascade');
            $table->integer('numero');
            $table->decimal('importe', 12, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'vencida', 'pagada', 'anulada'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->foreignId('movimiento_id')->nullable()->constrained('movimientos')->nullOnDelete();
            $table->dateTime('recordatorio_enviado_at')->nullable();
            $table->timestamps();

            $table->unique(['plan_pago_id', 'numero']);
            $table->index(['estado', 'fecha_vencimiento']);
            $table->index(['recordatorio_enviado_at', 'fecha_vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
