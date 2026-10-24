<?php

namespace Tests\Feature;

use App\Models\Asociado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsociadosTest extends TestCase
{
    use RefreshDatabase;

    private function datosAsociado(Asociado $asociado, array $cambios = []): array
    {
        return array_merge([
            'nombre' => $asociado->nombre,
            'email' => $asociado->email,
            'telefono' => null,
            'domicilio' => null,
        ], $cambios);
    }

    public function test_no_puede_cambiar_el_email_de_un_asociado_compartido_con_otra_organizacion(): void
    {
        [$organizacionA, $adminA] = $this->crearOrganizacionConAdmin();
        [, $adminB] = $this->crearOrganizacionConAdmin();
        $this->agregarMiembro($organizacionA, asociado: $adminB);

        Sanctum::actingAs($adminA);

        $this->putJson("/api/asociados/{$adminB->id}", $this->datosAsociado($adminB, ['email' => 'atacante@example.com']))
            ->assertStatus(400);

        $this->assertNotSame('atacante@example.com', $adminB->fresh()->email);
    }

    public function test_no_puede_cambiar_el_email_de_un_asociado_que_ya_ingreso(): void
    {
        [$organizacion, $admin] = $this->crearOrganizacionConAdmin();
        $socio = $this->agregarMiembro($organizacion, asociado: Asociado::factory()->conGoogle()->create());

        Sanctum::actingAs($admin);

        $this->putJson("/api/asociados/{$socio->id}", $this->datosAsociado($socio, ['email' => 'otro@example.com']))
            ->assertStatus(400);
    }

    public function test_puede_corregir_el_email_de_un_asociado_propio_que_nunca_ingreso(): void
    {
        [$organizacion, $admin] = $this->crearOrganizacionConAdmin();
        $socio = $this->agregarMiembro($organizacion);

        Sanctum::actingAs($admin);

        $this->putJson("/api/asociados/{$socio->id}", $this->datosAsociado($socio, ['email' => 'corregido@example.com']))
            ->assertOk();

        $this->assertSame('corregido@example.com', $socio->fresh()->email);
    }

    public function test_desactivar_y_activar_actualiza_la_membresia(): void
    {
        [$organizacion, $admin] = $this->crearOrganizacionConAdmin();
        $socio = $this->agregarMiembro($organizacion);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/asociados/{$socio->id}/desactivar")->assertOk();
        $membresia = $socio->organizaciones()->sole()->pivot;
        $this->assertFalse((bool) $membresia->activo);
        $this->assertNotNull($membresia->fecha_baja);

        $this->patchJson("/api/asociados/{$socio->id}/activar")->assertOk();
        $membresia = $socio->organizaciones()->sole()->pivot;
        $this->assertTrue((bool) $membresia->activo);
        $this->assertNull($membresia->fecha_baja);
    }

    public function test_admin_no_puede_darse_de_baja_a_si_mismo(): void
    {
        [, $admin] = $this->crearOrganizacionConAdmin();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/asociados/{$admin->id}/desactivar")->assertStatus(400);
    }
}
