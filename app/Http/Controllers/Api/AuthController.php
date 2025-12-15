<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Organizacion\OrganizacionDTO;
use App\DTOs\Asociado\AsociadoDTO;
use App\Http\Controllers\Controller;
use App\Mail\BienvenidaAsociado;
use App\Models\Asociado;
use App\Models\Organizacion;
use App\Services\AsociadoService;
use App\Services\OrganizacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


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
     *      - Revisa organizaciones vinculadas (solo activas)
     *      - Resuelve organización seleccionada
     *      - Devuelve token + status de flujo para el front
     */
    public function googleLogin(Request $request): JsonResponse
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

        $email        = $payload['email'];
        $nombreGoogle = $payload['name'] ?? $email;

        // 3) Buscar asociado por email (NO crear si no existe)
        /** @var \App\Models\Asociado|null $asociado */
        $asociado = Asociado::where('email', $email)->first();

        if (! $asociado) {
            // Usuario nuevo: no hay asociado todavía → flujo de signup
            return response()->json([
                'usuario'                      => [
                    'id'    => null,
                    'name'  => $nombreGoogle,
                    'email' => $email,
                ],
                'status'                       => 'NEEDS_SIGNUP_FORM',
                'organizaciones'               => [],
                'message'                      => null,
                'organizacion_seleccionada_id' => null,
            ]);
        }

        // 4) Actualizar algunos datos del perfil
        $asociado->nombre    = $nombreGoogle;
        $asociado->google_id = $payload['sub'] ?? $asociado->google_id;
        $asociado->save();

        // 5) Cargar organizaciones (con pivot) y filtrar solo las activas en pivot
        $asociado->load('organizaciones');
        $organizacionesActivas = $asociado->organizaciones
            ->filter(fn($org) => (bool) $org->pivot->activo);

        $orgCount  = $organizacionesActivas->count();
        $adminOrgs = $organizacionesActivas->filter(
            fn($org) => (bool) $org->pivot->es_admin
        );

        // 6) Resolver organización seleccionada (solo entre activas)
        $orgSeleccionada = $this->resolveOrganizacionSeleccionadaForAsociado($asociado);

        // 7) Determinar status del flujo
        $status  = 'NEEDS_SIGNUP_FORM';
        $message = null;

        if ($orgCount === 0) {
            // No tiene organizaciones activas → lo tratamos como que debe crear org
            $status = 'NEEDS_SIGNUP_FORM';
        } elseif ($orgCount === 1) {
            $org = $organizacionesActivas->first();
            if ($org->pivot->es_admin) {
                $status = 'DIRECT_LOGIN';
            } else {
                $status  = 'ASSOCIATE_ONLY';
                $message = 'El acceso para asociados todavía no está habilitado. Contacta al administrador de tu organización.';
            }
        } else {
            // Tiene 2+ organizaciones activas
            if ($adminOrgs->count() === 0) {
                $status  = 'ASSOCIATE_ONLY';
                $message = 'El acceso para asociados todavía no está habilitado. Contacta al administrador de tu organización.';
            } else {
                $status = 'NEEDS_ORG_SELECTION';
            }
        }

        // 8) Autenticar usuario con sesión (Sanctum stateful)
        auth()->login($asociado);

        // 9) Mapear organizaciones activas para el front
        $organizationsPayload = $organizacionesActivas->map(function ($org) {
            return [
                'id'               => $org->id,
                'nombre'           => $org->nombre,
                'fecha_alta'       => $org->fecha_alta,
                'es_prueba'        => (bool) $org->es_prueba,
                'fecha_fin_prueba' => $org->fecha_fin_prueba,
                'es_admin'         => (bool) $org->pivot->es_admin,
                'activo'           => (bool) $org->pivot->activo,
            ];
        })->values()->all();

        return response()->json([
            // No enviamos token, usamos cookies de sesión
            'usuario'                      => [
                'id'     => $asociado->id,
                'nombre' => $asociado->nombre,
                'email'  => $asociado->email,
            ],
            'status'                       => $status,
            'organizaciones'               => $organizationsPayload,
            'message'                      => $message,
            'organizacion_seleccionada_id' => $asociado->organizacion_seleccionada_id,
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
     * Crea una cuenta:
     * - Da de alta la organización.
     * - Crea un asociado.
     * - Vincula el asociado a la organización (pivot) como admin y activo.
     * - Marca esa organización como seleccionada en el asociado.
     */
    public function crearCuenta(Request $request): JsonResponse
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'nombre_usuario'      => 'required|string|max:255',
            'nombre_organizacion' => 'required|string|max:255',
            'email'               => 'required|email|max:255',
        ]);

        try {
            $resultado = DB::transaction(function () use ($validated) {
                // 1. Crear la organización usando el servicio
                $organizacionDTO = new OrganizacionDTO(
                    nombre: $validated['nombre_organizacion'],
                    fechaAlta: now()->format('Y-m-d'),
                    esPrueba: true,
                    fechaFinPrueba: now()->addDays(5)->format('Y-m-d')
                );

                /** @var \App\Models\Organizacion $organizacion */
                $organizacion = $this->organizacionService->crear($organizacionDTO);

                // 2. Crear el asociado directamente (sin servicio, ya que no hay usuario autenticado)
                $emailNormalizado = strtolower(trim($validated['email']));

                // Verificar que no exista el email
                if (Asociado::where('email', $emailNormalizado)->exists()) {
                    throw new \InvalidArgumentException('El email ya está registrado.');
                }

                /** @var \App\Models\Asociado $asociadoModel */
                $asociadoModel = Asociado::create([
                    'nombre' => trim($validated['nombre_usuario']),
                    'email' => $emailNormalizado,
                ]);

                // 3. Vincular el asociado con la organización en la tabla pivot
                $asociadoModel->organizaciones()->attach($organizacion->id, [
                    'fecha_alta' => now()->format('Y-m-d'),
                    'fecha_baja' => null,
                    'activo'     => true,   // membresía activa
                    'es_admin'   => true,   // es admin de esa organización
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Marcar la organización como seleccionada en el asociado
                $asociadoModel->organizacion_seleccionada_id = $organizacion->id;
                $asociadoModel->save();

                return [
                    'organizacion' => $organizacion,
                    'asociado'     => $asociadoModel,
                ];
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Error en la validación',
                'error'   => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear cuenta: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Error al crear la cuenta',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // Autenticar usuario con sesión (Sanctum stateful)
        auth()->login($resultado['asociado']);

        // Enviar email de bienvenida (fuera del try-catch de BD)
        try {
            Mail::send(new BienvenidaAsociado(
                $resultado['asociado'],
                $resultado['organizacion']->nombre
            ));
        } catch (\Exception $e) {
            Log::warning('Error al enviar email de bienvenida: ' . $e->getMessage(), [
                'asociado_id' => $resultado['asociado']->id,
            ]);
            // No hacemos que falle la respuesta si el email falla
        }

        return response()->json([
            'message' => 'Cuenta creada exitosamente',
            'data'    => [
                'organizacion' => [
                    'id'     => $resultado['organizacion']->id,
                    'nombre' => $resultado['organizacion']->nombre,
                    'es_prueba' => (bool) $resultado['organizacion']->es_prueba,
                    'fecha_fin_prueba' => $resultado['organizacion']->fecha_fin_prueba,
                ],
                'asociado'     => [
                    'id'     => $resultado['asociado']->id,
                    'nombre' => $resultado['asociado']->nombre,
                    'email'  => $resultado['asociado']->email,
                ],
                'organizacion_seleccionada_id' => $resultado['asociado']->organizacion_seleccionada_id,
            ],
        ], 201);
    }

    /**
     * Resuelve y setea la organización seleccionada del asociado.
     *
     * - Solo puede ser una organización donde el pivot exista y esté activo = true.
     * - Si ya tiene una seleccionada válida, la mantiene.
     * - Si no tiene, elige una por defecto (admin si hay, sino la primera activa).
     */
    protected function resolveOrganizacionSeleccionadaForAsociado(Asociado $asociado): ?Organizacion
    {
        $asociado->loadMissing('organizaciones');

        // Solo organizaciones con membresía activa
        $activas = $asociado->organizaciones->filter(
            fn($org) => (bool) $org->pivot->activo
        );

        if ($activas->isEmpty()) {
            $asociado->organizacion_seleccionada_id = null;
            $asociado->save();

            return null;
        }

        // Si ya tiene seleccionada y está entre las activas, la respetamos
        if ($asociado->organizacion_seleccionada_id) {
            $orgSeleccionada = $activas->firstWhere(
                'id',
                $asociado->organizacion_seleccionada_id
            );

            if ($orgSeleccionada) {
                return $orgSeleccionada;
            }
        }

        // Elegimos una por defecto: primero admin, si no la primera activa
        $org = $activas->firstWhere(fn($org) => (bool) $org->pivot->es_admin)
            ?? $activas->first();

        $asociado->organizacion_seleccionada_id = $org?->id;
        $asociado->save();

        return $org;
    }
}
