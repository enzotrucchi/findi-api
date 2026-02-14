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
            $table->string('codigo_acceso', 6)->nullable()->after('nombre');
            $table->index('codigo_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizaciones', function (Blueprint $table) {
            $table->dropIndex(['codigo_acceso']);
            $table->dropColumn('codigo_acceso');
        });
    }
};
