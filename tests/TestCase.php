<?php

namespace Tests;

use App\Models\Asociado;
use App\Models\Organizacion;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Los tests refrescan la base: nunca deben correr contra MySQL (stage/producción).
        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Los tests solo pueden correr sobre SQLite en memoria. Revisá phpunit.xml.');
        }
    }

    /**
     * Crea una organización habilitada con su admin, que queda con la organización seleccionada.
     *
     * @return array{0: Organizacion, 1: Asociado}
     */
    protected function crearOrganizacionConAdmin(array $atributosOrganizacion = []): array
    {
        $organizacion = Organizacion::factory()->create($atributosOrganizacion);
        $admin = $this->agregarMiembro($organizacion, esAdmin: true);

        return [$organizacion, $admin];
    }

    /**
     * Agrega un asociado (nuevo o existente) a la organización y la deja seleccionada.
     */
    protected function agregarMiembro(
        Organizacion $organizacion,
        bool $esAdmin = false,
        ?Asociado $asociado = null,
        bool $activo = true,
    ): Asociado {
        $asociado ??= Asociado::factory()->create();

        $asociado->organizaciones()->attach($organizacion->id, [
            'fecha_alta' => now()->toDateString(),
            'activo' => $activo,
            'es_admin' => $esAdmin,
            'primer_login_completado' => true,
        ]);

        $asociado->forceFill(['organizacion_seleccionada_id' => $organizacion->id])->save();

        return $asociado->fresh();
    }
}
