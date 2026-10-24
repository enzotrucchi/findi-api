<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * activo y es_admin del pivot quedaron nullables en las bases existentes y la baja de
 * asociados guardaba activo = null. Se normalizan a booleanos no nulos.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('asociado_organizacion')->whereNull('activo')->update(['activo' => false]);
        DB::table('asociado_organizacion')->whereNull('es_admin')->update(['es_admin' => false]);

        Schema::table('asociado_organizacion', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->nullable(false)->change();
            $table->boolean('es_admin')->default(false)->nullable(false)->change();
        });
    }

    /**
     * Irreversible: no se distinguen los null originales.
     */
    public function down(): void
    {
    }
};
