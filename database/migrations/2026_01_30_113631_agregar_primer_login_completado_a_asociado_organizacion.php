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
        Schema::table('asociado_organizacion', function (Blueprint $table) {
            $table->boolean('primer_login_completado')->default(false)->after('es_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asociado_organizacion', function (Blueprint $table) {
            $table->dropColumn('primer_login_completado');
        });
    }
};
