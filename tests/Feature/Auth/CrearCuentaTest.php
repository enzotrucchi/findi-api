<?php

namespace Tests\Feature\Auth;

use App\Models\Asociado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CrearCuentaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config([
            'services.google.client_id' => 'cliente-test',
            'sanctum.stateful' => ['localhost'],
        ]);
        // Requests "desde el frontend" para que Sanctum use sesión.
        $this->withHeader('Referer', 'http://localhost');
    }

    private function fakeGoogle(string $email): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response([
                'aud' => 'cliente-test',
                'sub' => '1234567890',
                'email' => $email,
                'email_verified' => 'true',
                'name' => 'Persona Test',
            ]),
        ]);
    }

    public function test_sin_login_de_google_no_puede_crear_cuenta(): void
    {
        $this->postJson('/api/auth/crear-cuenta', [
            'nombre_usuario' => 'Atacante',
            'nombre_organizacion' => 'Org falsa',
            'email' => 'victima@example.com',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('organizaciones', 0);
        $this->assertGuest();
    }

    public function test_ignora_el_email_del_body_y_usa_el_verificado_por_google(): void
    {
        [, $victima] = $this->crearOrganizacionConAdmin();
        $this->fakeGoogle('atacante@example.com');

        $this->postJson('/api/auth/google', ['id_token' => 'token', 'intent' => 'signup'])
            ->assertOk()
            ->assertJsonPath('status', 'NEEDS_SIGNUP_FORM');

        $this->postJson('/api/auth/crear-cuenta', [
            'nombre_usuario' => 'Atacante',
            'nombre_organizacion' => 'Org nueva',
            'email' => $victima->email,
        ])->assertCreated()
            ->assertJsonPath('usuario.email', 'atacante@example.com');

        $this->assertAuthenticatedAs(Asociado::where('email', 'atacante@example.com')->sole());
        $this->assertSame(1, $victima->organizaciones()->count());
    }

    public function test_flujo_completo_crea_organizacion_con_admin(): void
    {
        $this->fakeGoogle('Nueva@Example.com');

        $this->postJson('/api/auth/google', ['id_token' => 'token', 'intent' => 'signup'])->assertOk();

        $this->postJson('/api/auth/crear-cuenta', [
            'nombre_usuario' => 'Tesorera',
            'nombre_organizacion' => 'Club Test',
        ])->assertCreated();

        $asociado = Asociado::where('email', 'nueva@example.com')->sole();
        $organizacion = $asociado->organizaciones()->sole();

        $this->assertSame('Club Test', $organizacion->nombre);
        $this->assertTrue((bool) $organizacion->pivot->es_admin);
        $this->assertSame($organizacion->id, $asociado->organizacion_seleccionada_id);

        // El token verificado se consume: no sirve para una segunda cuenta.
        $this->postJson('/api/auth/crear-cuenta', [
            'nombre_usuario' => 'Tesorera',
            'nombre_organizacion' => 'Otra org',
        ])->assertUnauthorized();
    }
}
