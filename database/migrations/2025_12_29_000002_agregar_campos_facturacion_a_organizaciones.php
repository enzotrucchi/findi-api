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
        Schema::table('organizaciones', function (Blueprint $table) {
            // Agregar fecha_vencimiento para control de acceso
            $table->date('fecha_vencimiento')->nullable()->after('fecha_fin_prueba');

            // Agregar índice
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizaciones', function (Blueprint $table) {
            $table->dropIndex(['fecha_vencimiento']);
            $table->dropColumn(['fecha_vencimiento']);
        });
    }
};
