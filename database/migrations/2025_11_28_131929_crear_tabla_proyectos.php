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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->text('descripcion');
            $table->decimal('monto_actual', 10, 2)->default(0);
            $table->decimal('monto_objetivo', 10, 2);
            $table->date('fecha_alta');
            $table->date('fecha_realizacion')->nullable();
            $table->timestamps();

            $table->index('fecha_alta');
            $table->index('fecha_realizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
