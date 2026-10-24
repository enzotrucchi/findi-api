<?php

namespace Tests\Feature;

use App\Models\Organizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccesoOrganizacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_solo_las_organizaciones_del_usuario(): void
    {
        [$propia, $admin] = $this->crearOrganizacionConAdmin();
        Organizacion::factory()->count(2)->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/organizaciones')
            ->assertOk()
            ->assertJsonCount(1, 'datos')
            ->assertJsonPath('datos.0.id', $propia->id);
    }

    public function test_no_puede_ver_una_organizacion_ajena(): void
    {
        [, $admin] = $this->crearOrganizacionConAdmin();
        $ajena = Organizacion::factory()->create();

        Sanctum::actingAs($admin);

        $this->getJson("/api/organizaciones/{$ajena->id}")->assertNotFound();
    }

    public function test_no_existe_endpoint_para_borrar_organizaciones(): void
    {
        [, $admin] = $this->crearOrganizacionConAdmin();
        $ajena = Organizacion::factory()->create();

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/organizaciones/{$ajena->id}")->assertStatus(405);
        $this->assertModelExists($ajena);
    }

    public function test_admin_solo_cambia_el_nombre_y_no_la_prueba(): void
    {
        [$organizacion, $admin] = $this->crearOrganizacionConAdmin();
        $finPrueba = $organizacion->fecha_fin_prueba;

        Sanctum::actingAs($admin);

        $this->putJson("/api/organizaciones/{$organizacion->id}", [
            'nombre' => 'club nuevo',
            'es_prueba' => false,
            'fecha_fin_prueba' => '2099-12-31',
            'habilitada' => true,
        ])->assertOk();

        $organizacion->refresh();
        $this->assertSame('Club Nuevo', $organizacion->nombre);
        $this->assertTrue($organizacion->es_prueba);
        $this->assertSame($finPrueba, $organizacion->fecha_fin_prueba);
    }

    public function test_admin_no_puede_actualizar_otra_organizacion(): void
    {
        [, $admin] = $this->crearOrganizacionConAdmin();
        [$otra] = $this->crearOrganizacionConAdmin();

        Sanctum::actingAs($admin);

        $this->putJson("/api/organizaciones/{$otra->id}", ['nombre' => 'Hackeada'])->assertForbidden();
        $this->assertNotSame('Hackeada', $otra->fresh()->nombre);
    }

    public function test_socio_puede_leer_pero_no_escribir(): void
    {
        [$organizacion] = $this->crearOrganizacionConAdmin();
        $socio = $this->agregarMiembro($organizacion);

        Sanctum::actingAs($socio);

        $this->getJson('/api/movimientos')->assertOk();
        $this->getJson('/api/movimientos/balance')->assertOk();

        $this->postJson('/api/movimientos', [])->assertForbidden()->assertJsonPath('status', 'FORBIDDEN');
        $this->getJson('/api/asociados')->assertForbidden();
        $this->getJson('/api/organizaciones/codigo-acceso')->assertForbidden();
    }

    public function test_socio_sin_codigo_de_acceso_validado_no_accede(): void
    {
        [$organizacion] = $this->crearOrganizacionConAdmin();
        $socio = $this->agregarMiembro($organizacion);
        $socio->organizaciones()->updateExistingPivot($organizacion->id, ['primer_login_completado' => false]);

        Sanctum::actingAs($socio);

        $this->getJson('/api/movimientos')->assertForbidden()->assertJsonPath('status', 'NEEDS_ACCESS_CODE');
    }

    public function test_organizacion_deshabilitada_no_accede(): void
    {
        [, $admin] = $this->crearOrganizacionConAdmin(['habilitada' => false]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/movimientos')->assertForbidden()->assertJsonPath('status', 'ORG_DISABLED');
    }

    public function test_prueba_vencida_no_accede(): void
    {
        [, $admin] = $this->crearOrganizacionConAdmin([
            'es_prueba' => true,
            'fecha_fin_prueba' => now()->subDay()->toDateString(),
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/movimientos')->assertForbidden()->assertJsonPath('status', 'TRIAL_EXPIRED');
    }

    public function test_miembro_dado_de_baja_no_accede_aunque_la_tenga_seleccionada(): void
    {
        [$organizacion] = $this->crearOrganizacionConAdmin();
        $exSocio = $this->agregarMiembro($organizacion, esAdmin: true, activo: false);

        Sanctum::actingAs($exSocio);

        $this->getJson('/api/movimientos')->assertForbidden()->assertJsonPath('status', 'NOT_A_MEMBER');
    }

    public function test_validar_codigo_de_acceso_tiene_limite_de_intentos(): void
    {
        [$organizacion] = $this->crearOrganizacionConAdmin();
        $socio = $this->agregarMiembro($organizacion);

        Sanctum::actingAs($socio);

        foreach (range(1, 5) as $intento) {
            $this->postJson('/api/auth/validar-codigo-acceso', ['codigo_acceso' => 'XXXXXX'])->assertForbidden();
        }

        $this->postJson('/api/auth/validar-codigo-acceso', ['codigo_acceso' => 'XXXXXX'])->assertStatus(429);
    }
}
