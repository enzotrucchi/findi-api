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
        Schema::create('asociados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('email', 255)->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('domicilio', 500)->nullable();
            $table->boolean('es_admin')->default(false);
            $table->boolean('activo')->default(true);

            // Campos para autenticación
            $table->string('password')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();

            $table->timestamps();

            $table->index('activo');
            $table->index('es_admin');
            $table->index('google_id');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('asociados');
    }
};
