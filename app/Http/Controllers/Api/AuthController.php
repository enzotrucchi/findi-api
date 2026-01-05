<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Organizacion\OrganizacionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizacion\SeleccionarOrganizacionRequest;
use App\Mail\BienvenidaAdmin;
use App\Models\Asociado;
use App\Models\Organizacion;
use App\Services\OrganizacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private OrganizacionService $organizacionService,
    ) {}

    public function seleccionarOrganizacion(SeleccionarOrganizacionRequest $request): JsonResponse
    {
        /** @var \App\Models\Asociado $user */
        $user = Auth::user();

        $orgId = (int) $request->input('organizacion_id');

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

        $org = Organizacion::find($orgId);

        if ($org && isset($org->habilitada) && ! (bool) $org->habilitada) {
            return response()->json([
                'ok' => false,
                'message' => 'La organización está deshabilitada. Contactanos a hola@findiapp.com',
            ], 403);
        }

        $user->organizacion_seleccionada_id = $orgId;
        $user->save();

        return response()->json([
            'ok' => true,
            'organizacion_seleccionada_id' => $orgId,
        ]);
    }

    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
            'intent'   => 'nullable|in:login,signup',
        ]);

        $intent = $request->input('intent', 'login'); // default: login
        $payload = $this->verifyGoogleToken($request->input('id_token'));

        if (($payload['email_verified'] ?? 'false') !== 'true') {
            throw ValidationException::withMessages([
                'email' => ['El email de Google no está verificado.'],
            ]);
        }

        $email        = $payload['email'];
        $nombreGoogle = $payload['name'] ?? $email;

        /** @var \App\Models\Asociado|null $asociado */
        $asociado = Asociado::where('email', $email)->first();

        // No existe -> signup (sin sesión)
        if (! $asociado) {
            return response()->json([
                'usuario' => [
                    'id' => null,
                    'nombre' => $nombreGoogle,
                    'email' => $email,
                ],
                'status' => 'NEEDS_SIGNUP_FORM',
                'organizaciones' => [],
                'message' => null,
                'organizacion_seleccionada_id' => null,
                'asociado_existente' => false,
            ]);
        }

        // Existe -> actualizar perfil
        $asociado->nombre    = $nombreGoogle;
        $asociado->google_id = $payload['sub'] ?? $asociado->google_id;
        $asociado->save();

        $asociado->load('organizaciones');

        $organizacionesActivas = $asociado->organizaciones
            ->filter(fn($org) => (bool) $org->pivot->activo);

        $activasCount = $organizacionesActivas->count();

        $organizationsPayload = $this->mapOrganizacionesPayload($organizacionesActivas);

        // Caso: 2+ org activas -> selección (siempre)
        if ($activasCount > 1) {
            auth()->login($asociado);

            return response()->json([
                'usuario' => [
                    'id' => $asociado->id,
                    'nombre' => $asociado->nombre,
                    'email' => $asociado->email,
                ],
                'status' => 'NEEDS_ORG_SELECTION',
                'organizaciones' => $organizationsPayload,
                'message' => null,
                'organizacion_seleccionada_id' => null,
                'asociado_existente' => true,
            ]);
        }

        // Caso: 0 org activas -> signup
        if ($activasCount === 0) {
            return response()->json([
                'usuario' => [
                    'id' => $asociado->id,
                    'nombre' => $asociado->nombre,
                    'email' => $asociado->email,
                ],
                'status' => 'NEEDS_SIGNUP_FORM',
                'organizaciones' => $organizationsPayload,
                'message' => 'Tu usuario existe, pero no tiene ninguna organización activa. Podés crear una nueva.',
                'organizacion_seleccionada_id' => null,
                'asociado_existente' => true,
            ]);
        }

        // Caso: 1 org activa
        $org = $organizacionesActivas->first();
        $orgHabilitada = (bool) ($org->habilitada ?? true);

        // Si está deshabilitada:
        if (! $orgHabilitada) {
            // LOGIN: quedarse en login (sin sesión)
            if ($intent === 'login') {
                return response()->json([
                    'usuario' => null,
                    'status' => 'ORG_DISABLED',
                    'organizaciones' => $organizationsPayload,
                    'message' => 'Tu cuenta está asociada a una organización deshabilitada. Contacta al administrador o crea una nueva cuenta.',
                    'organizacion_seleccionada_id' => null,
                    'asociado_existente' => true,
                ]);
            }

            // SIGNUP: permitir avanzar al form (sin sesión todavía)
            return response()->json([
                'usuario' => [
                    'id' => $asociado->id,
                    'nombre' => $asociado->nombre,
                    'email' => $asociado->email,
                ],
                'status' => 'NEEDS_SIGNUP_FORM',
                'organizaciones' => $organizationsPayload,
                'message' => 'Ya existe un usuario con este correo, pero su organización anterior está deshabilitada. Podés crear una nueva organización para continuar.',
                'organizacion_seleccionada_id' => null,
                'asociado_existente' => true,
            ]);
        }

        // Org habilitada: admin => login directo; no admin => asociado-only
        if (! (bool) $org->pivot->es_admin) {
            // sin sesión
            return response()->json([
                'usuario' => null,
                'status' => 'ASSOCIATE_ONLY',
                'organizaciones' => $organizationsPayload,
                'message' => 'El acceso para asociados todavía no está habilitado. Contacta al administrador de tu organización.',
                'organizacion_seleccionada_id' => null,
                'asociado_existente' => true,
                'usuario' => [
                    'id' => $asociado->id,
                    'nombre' => $asociado->nombre,
                    'email' => $asociado->email,
                ],
            ]);
        }

        // DIRECT_LOGIN (con sesión)
        auth()->login($asociado);

        if (! $asociado->organizacion_seleccionada_id || (int) $asociado->organizacion_seleccionada_id !== (int) $org->id) {
            $asociado->organizacion_seleccionada_id = $org->id;
            $asociado->save();
        }

        return response()->json([
            'usuario' => [
                'id' => $asociado->id,
                'nombre' => $asociado->nombre,
                'email' => $asociado->email,
            ],
            'status' => 'DIRECT_LOGIN',
            'organizaciones' => $organizationsPayload,
            'message' => null,
            'organizacion_seleccionada_id' => (int) $asociado->organizacion_seleccionada_id,
            'asociado_existente' => true,
        ]);
    }

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

    private function mapOrganizacionesPayload($organizacionesActivas): array
    {
        return $organizacionesActivas->map(function ($org) {
            // Obtener el asociado admin de la organización
            $adminAsociado = $org->asociados()
                ->wherePivot('es_admin', true)
                ->first();

            return [
                'id'               => $org->id,
                'nombre'           => $org->nombre,
                'fecha_alta'       => $org->fecha_alta,
                'es_prueba'        => (bool) $org->es_prueba,
                'fecha_fin_prueba' => $org->fecha_fin_prueba,
                'es_admin'         => (bool) $adminAsociado && $adminAsociado->id === auth()->id(),
                'activo'           => (bool) $org->pivot->activo,
                'habilitada'       => (bool) ($org->habilitada ?? true),
                'usuario_admin'    => $adminAsociado ? [
                    'id'     => $adminAsociado->id,
                    'nombre' => $adminAsociado->nombre,
                    'email'  => $adminAsociado->email,
                ] : null,
            ];
        })->values()->all();
    }

    public function crearCuenta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre_usuario'      => 'required|string|max:255',
            'nombre_organizacion' => 'required|string|max:255',
            'email'               => 'required|email|max:255',
        ]);

        try {
            $resultado = DB::transaction(function () use ($validated) {
                $organizacionDTO = new OrganizacionDTO(
                    nombre: $validated['nombre_organizacion'],
                    fechaAlta: now()->format('Y-m-d'),
                    esPrueba: true,
                    fechaFinPrueba: now()->addDays(5)->format('Y-m-d')
                );

                $organizacion = $this->organizacionService->crear($organizacionDTO);

                $emailNormalizado = strtolower(trim($validated['email']));

                $asociadoModel = Asociado::where('email', $emailNormalizado)->first();

                if (! $asociadoModel) {
                    $asociadoModel = Asociado::create([
                        'nombre' => trim($validated['nombre_usuario']),
                        'email'  => $emailNormalizado,
                    ]);
                }

                $asociadoModel->organizaciones()->attach($organizacion->id, [
                    'fecha_alta' => now()->format('Y-m-d'),
                    'fecha_baja' => null,
                    'activo'     => true,
                    'es_admin'   => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $asociadoModel->organizacion_seleccionada_id = $organizacion->id;
                $asociadoModel->save();

                return [
                    'organizacion' => $organizacion,
                    'asociado' => $asociadoModel,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error al crear cuenta: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al crear la cuenta',
                'error' => $e->getMessage(),
            ], 500);
        }

        auth()->login($resultado['asociado']);

        try {
            Mail::send(new BienvenidaAdmin(
                $resultado['asociado'],
                $resultado['organizacion']->nombre
            ));
        } catch (\Exception $e) {
            Log::warning('Error al enviar email de bienvenida: ' . $e->getMessage(), [
                'asociado_id' => $resultado['asociado']->id,
            ]);
        }

        $resultado['asociado']->load('organizaciones');
        $organizacionesActivas = $resultado['asociado']->organizaciones
            ->filter(fn($org) => (bool) $org->pivot->activo);

        return response()->json([
            'usuario' => [
                'id' => $resultado['asociado']->id,
                'nombre' => $resultado['asociado']->nombre,
                'email' => $resultado['asociado']->email,
            ],
            'status' => 'DIRECT_LOGIN',
            'organizaciones' => $this->mapOrganizacionesPayload($organizacionesActivas),
            'message' => null,
            'organizacion_seleccionada_id' => (int) $resultado['asociado']->organizacion_seleccionada_id,
        ], 201);
    }
}
