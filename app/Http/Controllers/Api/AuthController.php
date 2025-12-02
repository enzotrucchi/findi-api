<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Asociado\CrearAsociadoDTO;
use App\DTOs\Organizacion\CrearOrganizacionDTO;
use App\Http\Controllers\Controller;
use App\Models\Asociado;
use App\Services\AsociadoService;
use App\Services\OrganizacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private OrganizacionService $organizacionService,
        private AsociadoService $asociadoService,
    ) {}

    /**
     * Login/entrada con Google.
     *
     * - Valida id_token
     * - Si el email NO existe -> NO crea asociado, solo indica que debe ir a signup
     * - Si el email existe:
     *      - Revisa organizaciones vinculadas
     *      - Devuelve token + status de flujo para el front
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->input('id_token');

        // 1) Verificar el token contra Google
        $payload = $this->verifyGoogleToken($idToken);

        // 2) Verificar email verificado
        if (($payload['email_verified'] ?? 'false') !== 'true') {
            throw ValidationException::withMessages([
                'email' => ['El email de Google no está verificado.'],
            ]);
        }

        $email = $payload['email'];
        $nombreGoogle = $payload['name'] ?? $email;

        // 3) Buscar asociado por email (NO crear si no existe)
        /** @var \App\Models\Asociado|null $asociado */
        $asociado = Asociado::where('email', $email)->first();

        if (! $asociado) {
            // Usuario nuevo: no hay asociado todavía → NO insertamos nada
            // Front debe ir al flujo de "crear cuenta" con este mail
            return response()->json([
                'token'         => null,
                'user'          => [
                    'id'    => null,
                    'name'  => $nombreGoogle,
                    'email' => $email,
                ],
                'status'        => 'NEEDS_SIGNUP_FORM',
                'organizations' => [],
                'message'       => null,
            ]);
        }

        // 4) Asociado existente: podemos actualizar algunos datos de perfil si querés
        $asociado->nombre    = $nombreGoogle;
        $asociado->google_id = $payload['sub'] ?? $asociado->google_id;
        $asociado->save();

        // 5) Obtener organizaciones activas del asociado
        $organizaciones = $asociado->organizaciones()
            ->wherePivot('activo', true)
            ->get();

        $orgCount  = $organizaciones->count();
        $adminOrgs = $organizaciones->filter(fn($org) => (bool) $org->pivot->es_admin);

        // 6) Determinar status del flujo
        $status  = 'NEEDS_SIGNUP_FORM';
        $message = null;

        if ($orgCount === 0) {
            // Asociado sin organizaciones → tratamos como "debe crear organización"
            $status = 'NEEDS_SIGNUP_FORM';
        } elseif ($orgCount === 1) {
            $org = $organizaciones->first();
            if ($org->pivot->es_admin) {
                $status = 'DIRECT_LOGIN';
            } else {
                $status  = 'ASSOCIATE_ONLY';
                $message = 'El acceso para asociados todavía no está habilitado. Contacta al administrador de tu organización.';
            }
        } else {
            // Tiene 2+ organizaciones
            if ($adminOrgs->count() === 0) {
                $status  = 'ASSOCIATE_ONLY';
                $message = 'El acceso para asociados todavía no está habilitado. Contacta al administrador de tu organización.';
            } else {
                $status = 'NEEDS_ORG_SELECTION';
            }
        }

        // 7) Crear token de acceso (Sanctum)
        $token = $asociado->createToken('findi-pwa')->plainTextToken;

        // 8) Mapear organizaciones para el front
        $organizationsPayload = $organizaciones->map(function ($org) {
            return [
                'id'   => $org->id,
                'name' => $org->nombre,
                'role' => $org->pivot->es_admin ? 'admin' : 'associate',
            ];
        })->values()->all();

        // 9) Responder al front
        return response()->json([
            'token'         => $token,
            'user'          => [
                'id'    => $asociado->id,
                'name'  => $asociado->nombre,
                'email' => $asociado->email,
            ],
            'status'        => $status,
            'organizations' => $organizationsPayload,
            'message'       => $message,
        ]);
    }

    /**
     * Verifica el id_token con Google usando el endpoint tokeninfo.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function verifyGoogleToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'id_token' => ['No se pudo validar el token de Google.'],
            ]);
        }

        $data = $response->json();

        if (! isset($data['aud']) || $data['aud'] !== $clientId) {
            throw ValidationException::withMessages([
                'id_token' => ['El token de Google no corresponde a este cliente.'],
            ]);
        }

        return $data;
    }

    /**
     * Crea una cuenta
     * Da de alta la organización.
     * Se crea un asociado vinculado a la organización en asociado_organizacion como admin.
     *
     * IMPORTANTE: acá sí se da de alta el Asociado, ya que al llegar a este
     * punto el usuario eligió "Crear cuenta" y completó nombre + organización.
     */
    public function crearCuenta(Request $request)
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'nombre_usuario'      => 'required|string|max:255',
            'nombre_organizacion' => 'required|string|max:255',
            'email'               => 'required|email|max:255',
        ]);

        try {
            // Ejecutar toda la lógica dentro de una transacción
            $resultado = DB::transaction(function () use ($validated) {
                // 1. Crear la organización usando el servicio
                $organizacionDTO = new CrearOrganizacionDTO(
                    nombre: $validated['nombre_organizacion'],
                    fechaAlta: now()->format('Y-m-d'),
                    esPrueba: true,
                    fechaFinPrueba: now()->addDays(5)->format('Y-m-d')
                );

                $organizacion = $this->organizacionService->crear($organizacionDTO);

                // 2. Crear el asociado usando el servicio
                $asociadoDTO = new CrearAsociadoDTO(
                    nombre: $validated['nombre_usuario'],
                    email: $validated['email'],
                );

                $asociado = $this->asociadoService->crear($asociadoDTO);

                // 3. Vincular el asociado con la organización en la tabla pivot
                $asociadoModel = Asociado::findOrFail($asociado->id);
                $asociadoModel->organizaciones()->attach($organizacion->id, [
                    'fecha_alta' => now()->format('Y-m-d'),
                    'activo'     => true,
                    'es_admin'   => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [
                    'organizacion' => $organizacion,
                    'asociado'     => $asociadoModel,
                ];
            });

            // (Opcional) Crear token para loguear al usuario inmediatamente después del signup
            $token = $resultado['asociado']->createToken('findi-pwa')->plainTextToken;

            // Responder con los datos creados
            return response()->json([
                'message' => 'Cuenta creada exitosamente',
                'data'    => [
                    'organizacion' => [
                        'id'     => $resultado['organizacion']->id,
                        'nombre' => $resultado['organizacion']->nombre,
                    ],
                    'asociado'     => [
                        'id'    => $resultado['asociado']->id,
                        'nombre' => $resultado['asociado']->nombre,
                        'email' => $resultado['asociado']->email,
                    ],
                    'token' => $token,
                ],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            // Errores de validación de negocio
            return response()->json([
                'message' => 'Error en la validación',
                'error'   => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            // Cualquier otro error
            return response()->json([
                'message' => 'Error al crear la cuenta',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
