<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->foreignId('organizacion_id')
                ->after('id')
                ->constrained('organizaciones')
                ->onDelete('cascade');

            // Índice para mejorar las consultas por organización
            $table->index('organizacion_id');
        });

        // Eliminar el índice unique de email y crear uno compuesto
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['organizacion_id', 'email']);
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropUnique(['organizacion_id', 'email']);
            $table->unique('email');
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropForeign(['organizacion_id']);
            $table->dropIndex(['organizacion_id']);
            $table->dropColumn('organizacion_id');
        });
    }
};
