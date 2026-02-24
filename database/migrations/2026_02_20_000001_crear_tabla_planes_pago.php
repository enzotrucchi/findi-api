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
        Schema::create('planes_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');
            $table->foreignId('asociado_id')->constrained('asociados')->onDelete('cascade');
            $table->string('descripcion');
            $table->decimal('total', 12, 2);
            $table->enum('estado', ['activo', 'finalizado', 'cancelado'])->default('activo');
            $table->timestamps();

            $table->index(['organizacion_id', 'estado']);
            $table->index(['asociado_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_pago');
    }
};
