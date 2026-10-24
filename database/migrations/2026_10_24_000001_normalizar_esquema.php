<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea el esquema con lo que usa el código.
 *
 * Las bases existentes (stage/producción) se modificaron con migraciones que después se
 * borraron del repo, así que el esquema real y el que generan las migraciones divergían.
 * Esta migración es idempotente: deja en el mismo estado una base nueva y una existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->renombrarComentariosDeProyecto();
        $this->normalizarOrganizaciones();
        $this->normalizarOrganizacionSeleccionada();
        $this->normalizarProyectos();
        $this->eliminarColumnasSinUso();
        $this->eliminarIndiceEmailDuplicado();

        Schema::dropIfExists('users');
    }

    /**
     * Irreversible: normaliza datos y elimina columnas sin uso.
     */
    public function down(): void
    {
    }

    private function renombrarComentariosDeProyecto(): void
    {
        if (Schema::hasTable('proyecto_historial') && ! Schema::hasTable('proyecto_comentarios')) {
            Schema::rename('proyecto_historial', 'proyecto_comentarios');
        }
    }

    private function normalizarOrganizaciones(): void
    {
        if (! Schema::hasColumn('organizaciones', 'habilitada')) {
            Schema::table('organizaciones', function (Blueprint $table) {
                $table->boolean('habilitada')->default(true)->after('fecha_vencimiento');
            });
        }

        DB::table('organizaciones')->whereNull('habilitada')->update(['habilitada' => true]);
        DB::table('organizaciones')->whereNull('es_prueba')->update(['es_prueba' => false]);

        Schema::table('organizaciones', function (Blueprint $table) {
            $table->boolean('habilitada')->default(true)->nullable(false)->change();
            $table->boolean('es_prueba')->default(false)->nullable(false)->change();
        });
    }

    private function normalizarOrganizacionSeleccionada(): void
    {
        if (! Schema::hasColumn('asociados', 'organizacion_seleccionada_id')) {
            Schema::table('asociados', function (Blueprint $table) {
                $table->foreignId('organizacion_seleccionada_id')
                    ->nullable()
                    ->constrained('organizaciones')
                    ->nullOnDelete();
            });

            return;
        }

        DB::table('asociados')
            ->whereNotNull('organizacion_seleccionada_id')
            ->whereNotIn('organizacion_seleccionada_id', DB::table('organizaciones')->select('id'))
            ->update(['organizacion_seleccionada_id' => null]);

        Schema::table('asociados', function (Blueprint $table) {
            $table->unsignedBigInteger('organizacion_seleccionada_id')->nullable()->change();
        });

        if (! $this->tieneForeignKey('asociados', 'organizacion_seleccionada_id')) {
            Schema::table('asociados', function (Blueprint $table) {
                $table->foreign('organizacion_seleccionada_id')
                    ->references('id')
                    ->on('organizaciones')
                    ->nullOnDelete();
            });
        }
    }

    private function normalizarProyectos(): void
    {
        if (! Schema::hasColumn('proyectos', 'organizacion_id')) {
            Schema::table('proyectos', function (Blueprint $table) {
                $table->foreignId('organizacion_id')
                    ->nullable()
                    ->constrained('organizaciones')
                    ->cascadeOnDelete();
            });
        } else {
            // Proyectos sin organización válida quedan invisibles (scope por organización);
            // se desvinculan en lugar de borrarse para no arrastrar sus movimientos.
            DB::table('proyectos')
                ->whereNotNull('organizacion_id')
                ->whereNotIn('organizacion_id', DB::table('organizaciones')->select('id'))
                ->update(['organizacion_id' => null]);

            Schema::table('proyectos', function (Blueprint $table) {
                $table->unsignedBigInteger('organizacion_id')->nullable()->change();
            });

            if (! $this->tieneForeignKey('proyectos', 'organizacion_id')) {
                Schema::table('proyectos', function (Blueprint $table) {
                    $table->foreign('organizacion_id')
                        ->references('id')
                        ->on('organizaciones')
                        ->cascadeOnDelete();
                });
            }

            if (! $this->tieneIndice('proyectos', 'proyectos_organizacion_id_index')) {
                Schema::table('proyectos', function (Blueprint $table) {
                    $table->index('organizacion_id');
                });
            }
        }

        // Montos como decimal: float pierde precisión con dinero.
        DB::table('proyectos')->whereNull('monto_actual')->update(['monto_actual' => 0]);
        DB::table('proyectos')->whereNull('monto_objetivo')->update(['monto_objetivo' => 0]);

        Schema::table('proyectos', function (Blueprint $table) {
            $table->decimal('monto_actual', 12, 2)->default(0)->nullable(false)->change();
            $table->decimal('monto_objetivo', 12, 2)->default(0)->nullable(false)->change();
        });
    }

    /**
     * El rol de admin vive en asociado_organizacion.es_admin y el login es solo con Google.
     */
    private function eliminarColumnasSinUso(): void
    {
        $this->eliminarColumna('asociados', 'es_admin', 'asociados_es_admin_index');
        $this->eliminarColumna('asociados', 'password');
        $this->eliminarColumna('modos_pago', 'activo', 'modos_pago_activo_index');
        $this->eliminarColumna('modos_pago', 'descripcion');
    }

    private function eliminarIndiceEmailDuplicado(): void
    {
        if ($this->tieneIndice('asociados', 'asociados_pk') && $this->tieneIndice('asociados', 'asociados_email_unique')) {
            Schema::table('asociados', function (Blueprint $table) {
                $table->dropUnique('asociados_pk');
            });
        }
    }

    private function eliminarColumna(string $tabla, string $columna, ?string $indice = null): void
    {
        if (! Schema::hasColumn($tabla, $columna)) {
            return;
        }

        if ($indice && $this->tieneIndice($tabla, $indice)) {
            Schema::table($tabla, fn (Blueprint $table) => $table->dropIndex($indice));
        }

        Schema::table($tabla, fn (Blueprint $table) => $table->dropColumn($columna));
    }

    private function tieneIndice(string $tabla, string $indice): bool
    {
        $indices = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($tabla);

        return array_key_exists(strtolower($indice), array_change_key_case($indices));
    }

    private function tieneForeignKey(string $tabla, string $columna): bool
    {
        $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys($tabla);

        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->getLocalColumns() === [$columna]) {
                return true;
            }
        }

        return false;
    }
};
