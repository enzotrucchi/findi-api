<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Organizacion\OrganizacionDTO;
use App\DTOs\Asociado\AsociadoDTO;
use App\Http\Controllers\Controller;
use App\Mail\BienvenidaAdmin;
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
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Organizacion\SeleccionarOrganizacionRequest;

class AuthController extends Controller
{
    /**
     * Constructor con inyección de dependencias.
     */
    public function __construct(
        private OrganizacionService $organizacionService,
    ) {}

    public function seleccionarOrganizacion(SeleccionarOrganizacionRequest $request): JsonResponse
    {
        /** @var \App\Models\Asociado $user */
        $user = Auth::user();

        $orgId = (int) $request->input('organizacion_id');

        // Verificar que el user pertenezca a esa org (pivot)
        $pertenece = $user->organizaciones()
            ->where('organizacion_id', $orgId)
            ->wherePivot('activo', true)
            ->exists();

        if (! $pertenece) {
            return response()->json([
                'ok' => false,
                'message' => 'No perteneces a esa organización activa.',
            ], 403);
        }

        // Verificar que la organización no esté deshabilitada
        $org = Organizacion::find($orgId);
        if ($org && isset($org->habilitada) && ! (bool) $org->habilitada) {
            return response()->json([
                'ok' => false,
                'message' => 'La organización está deshabilitada y no puede seleccionarse.',
            ], 403);
        }

        // Persistir selección
        $user->organizacion_seleccionada_id = $orgId;
        $user->save();

        return response()->json([
            'ok' => true,
            'organizacion_seleccionada_id' => $orgId,
        ]);
    }

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
                    'nombre'  => $nombreGoogle,
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

        // 5) Cargar organizaciones (con pivot).
        // Enviamos al front todas las organizaciones con pivot->activo = true
        // (incluyendo las que tengan `habilitada = false`) para que el usuario
        // pueda ver que existen y su estado.
        $asociado->load('organizaciones');
        $organizacionesActivas = $asociado->organizaciones
            ->filter(fn($org) => (bool) $org->pivot->activo);

        // Subconjunto de organizaciones que además están habilitadas
        $organizacionesHabilitadas = $organizacionesActivas
            ->filter(fn($org) => (bool) ($org->habilitada ?? true));

        $orgCount  = $organizacionesHabilitadas->count();
        $adminOrgs = $organizacionesHabilitadas->filter(
            fn($org) => (bool) $org->pivot->es_admin
        );

        // Map para payload (incluye las orgs deshabilitadas para que el front las vea)
        $organizationsPayload = $organizacionesActivas->map(function ($org) {
            return [
                'id'               => $org->id,
                'nombre'           => $org->nombre,
                'fecha_alta'       => $org->fecha_alta,
                'es_prueba'        => (bool) $org->es_prueba,
                'fecha_fin_prueba' => $org->fecha_fin_prueba,
                'es_admin'         => (bool) $org->pivot->es_admin,
                'activo'           => (bool) $org->pivot->activo,
                'habilitada'       => (bool) ($org->habilitada ?? true),
            ];
        })->values()->all();

        // Si el usuario tiene organizaciones vinculadas pero ninguna está habilitada,
        // devolvemos un bloqueo (403) pero igualmente incluimos las organizaciones
        // en el payload para que el usuario vea que existen y su estado.
        $hasAnyOrg = $asociado->organizaciones->count() > 0;
        // if ($hasAnyOrg && $orgCount === 0) {
        //     return response()->json([
        //         'usuario' => null,
        //         'status'  => 'DISABLED_SELECTED',
        //         'organizaciones' => $organizationsPayload,
        //         'message' => 'La organización ha sido deshabilitada. Contacta al administrador.',
        //         'organizacion_seleccionada_id' => null,
        //     ], 403);
        // }

        // Nota: la comprobación de organización seleccionada deshabilitada se gestiona
        // más abajo según el número de organizaciones activas (si tiene 2+ orgs,
        // forzamos selección en lugar de bloquear aquí).

        // 6) Resolver organización seleccionada (solo entre activas)
        $orgSeleccionada = $this->resolveOrganizacionSeleccionadaForAsociado($asociado);

        // 7) Determinar status del flujo
        // - Usamos el conteo de organizaciones con pivot->activo (organizacionesActivas)
        //   para decidir si el usuario debe elegir organización cuando tiene más de una.
        // - Para permitir el login consideramos únicamente las organizaciones habilitadas.
        $status  = 'NEEDS_SIGNUP_FORM';
        $message = null;

        $activasCount = $organizacionesActivas->count();
        $habilitadasCount = $organizacionesHabilitadas->count();

        if ($habilitadasCount === 0) {
            // No hay ninguna organización habilitada -> debe crear/esperar (y antes ya devolvemos 403)
            $status = 'NEEDS_SIGNUP_FORM';
        } elseif ($activasCount === 0) {
            // No pertenece a ninguna organización activa
            $status = 'NEEDS_SIGNUP_FORM';
        } elseif ($activasCount === 1) {
            // Si sólo tiene 1 organización activa, permitimos login directo para admin,
            // o mostramos mensaje de asociado si no es admin (siempre y cuando la org esté habilitada)
            $org = $organizacionesActivas->first();
            if ((bool) ($org->habilitada ?? true)) {
                if ($org->pivot->es_admin) {
                    $status = 'DIRECT_LOGIN';
                } else {
                    $status  = 'ASSOCIATE_ONLY';
                    $message = 'El acceso para asociados todavía no está habilitado. Contacta al administrador de tu organización.';
                }
            } else {
                // La única org activa está deshabilitada -> se maneja más arriba (403), pero como fallback:
                $status = 'NEEDS_SIGNUP_FORM';
            }
        } else {
            // Tiene 2+ organizaciones activas -> siempre forzamos selección de org
            $status = 'NEEDS_ORG_SELECTION';
        }

        // 8) Autenticar usuario con sesión (Sanctum stateful)
        auth()->login($asociado);

        // Decidir qué organizacion_seleccionada_id devolver en la respuesta:
        // - Si el usuario tiene más de una organización activa, forzamos null
        //   para que el frontend muestre el selector y no se confíe en la última seleccionada.
        $responseSelectedId = $asociado->organizacion_seleccionada_id;
        if ($activasCount > 1) {
            $responseSelectedId = null;
        }

        // organizationsPayload ya fue construido arriba (incluye orgs deshabilitadas)

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
            'organizacion_seleccionada_id' => $responseSelectedId,
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

                // 2. Buscar o crear el asociado
                $emailNormalizado = strtolower(trim($validated['email']));

                /** @var \App\Models\Asociado $asociadoModel */
                $asociadoModel = Asociado::where('email', $emailNormalizado)->first();

                $asociadoExistente = !is_null($asociadoModel);

                if (!$asociadoExistente) {
                    // Crear nuevo asociado solo si no existe
                    $asociadoModel = Asociado::create([
                        'nombre' => trim($validated['nombre_usuario']),
                        'email' => $emailNormalizado,
                    ]);
                }

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
                    'asociado_existente' => $asociadoExistente,
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
            Mail::send(new BienvenidaAdmin(
                $resultado['asociado'],
                $resultado['organizacion']->nombre
            ));
        } catch (\Exception $e) {
            Log::warning('Error al enviar email de bienvenida: ' . $e->getMessage(), [
                'asociado_id' => $resultado['asociado']->id,
            ]);
            // No hacemos que falle la respuesta si el email falla
        }

        $resultado['asociado']->load('organizaciones');
        $resultado['asociado']->load('organizaciones');

        // En crearCuenta devolvemos todas las organizaciones con pivot->activo = true
        // (incluyendo las deshabilitadas) para que el frontend las muestre.
        $organizacionesActivas = $resultado['asociado']->organizaciones
            ->filter(fn($org) => (bool) $org->pivot->activo);

        $organizationsPayload = $organizacionesActivas->map(function ($org) {
            return [
                'id'               => $org->id,
                'nombre'           => $org->nombre,
                'fecha_alta'       => $org->fecha_alta,
                'es_prueba'        => (bool) $org->es_prueba,
                'fecha_fin_prueba' => $org->fecha_fin_prueba,
                'es_admin'         => (bool) $org->pivot->es_admin,
                'activo'           => (bool) $org->pivot->activo,
                'habilitada'       => (bool) ($org->habilitada ?? true),
            ];
        })->values()->all();

        return response()->json([
            'usuario' => [
                'id'     => $resultado['asociado']->id,
                'nombre' => $resultado['asociado']->nombre,
                'email'  => $resultado['asociado']->email,
            ],
            'status' => 'DIRECT_LOGIN',
            'organizaciones' => $organizationsPayload,
            'message' => null,
            'organizacion_seleccionada_id' => $resultado['asociado']->organizacion_seleccionada_id,
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

        // Solo organizaciones con membresía activa y además habilitadas
        $activas = $asociado->organizaciones->filter(
            fn($org) => (bool) $org->pivot->activo && (bool) ($org->habilitada ?? true)
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
