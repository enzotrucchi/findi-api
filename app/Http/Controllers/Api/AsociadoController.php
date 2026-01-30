<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AsociadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\DTOs\Asociado\FiltroAsociadoDTO;
use App\Models\Asociado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Asociado\AsociadoRequest;
use App\DTOs\Asociado\AsociadoDTO;
use App\Http\Requests\Asociado\UpdateMeRequest;


class AsociadoController extends Controller
{
    public function __construct(private AsociadoService $asociadoService) {}

    public function obtener(int $id): JsonResponse
    {
        try {
            $asociado = $this->asociadoService->obtenerPorId($id);

            if (!$asociado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($asociado, 'Asociado obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener asociado: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener colección de asociados (paginada) de la organización seleccionada.
     *
     * @return LengthAwarePaginator
     */
    public function obtenerColeccion(FiltroAsociadoDTO $filtroDTO): JsonResponse
    {
        try {
            $filtroDTO = new FiltroAsociadoDTO();
            $filtroDTO->setPagina(request()->input('pagina', 1));
            $filtroDTO->setSearch(request()->input('search', null));

            $asociados = $this->asociadoService->obtenerColeccion($filtroDTO);

            return ApiResponse::exito($asociados, 'Asociados obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener asociados: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/asociados/todos
     * Obtener todos los asociados sin paginación (para selects/checkboxes)
     */
    public function todos(): JsonResponse
    {
        try {
            $asociados = $this->asociadoService->obtenerTodos();
            return ApiResponse::exito($asociados, 'Asociados obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener asociados: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->asociadoService->obtenerEstadisticas();
            return ApiResponse::exito($estadisticas, 'Estadísticas de asociados obtenidas');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerMovimientos(int $id): JsonResponse
    {
        try {
            $movimientos = $this->asociadoService->obtenerMovimientos($id);

            if ($movimientos === null) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($movimientos, 'Movimientos del asociado obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener movimientos: ' . $e->getMessage(), 500);
        }
    }

    public function crear(AsociadoRequest $request): JsonResponse
    {
        try {
            $dto = AsociadoDTO::desdeArray($request->validated());

            $asociado = $this->asociadoService->crear($dto);

            return ApiResponse::creado($asociado, 'Asociado creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear asociado: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(int $id, AsociadoRequest $request): JsonResponse
    {
        try {
            $dto = AsociadoDTO::desdeArray($request->validated());

            $asociado = $this->asociadoService->actualizar($id, $dto);

            if (!$asociado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($asociado, 'Asociado actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar asociado: ' . $e->getMessage(), 500);
        }
    }

    public function activar(int $id): JsonResponse
    {
        try {
            $asociado = $this->asociadoService->activar($id);

            if (!$asociado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($asociado, 'Asociado activado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al activar asociado: ' . $e->getMessage(), 500);
        }
    }

    public function desactivar(int $id): JsonResponse
    {
        try {
            $asociado = $this->asociadoService->desactivar($id);

            if (!$asociado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($asociado, 'Asociado desactivado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al desactivar asociado: ' . $e->getMessage(), 500);
        }
    }

    public function actualizarPerfil(UpdateMeRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizacionActualizada = null;
        $actualizoUsuario = false;

        if ($request->filled('nombre')) {
            $user->nombre = $request->string('nombre')->trim();
            $actualizoUsuario = true;
        }

        if ($request->filled('organizacion_nombre')) {
            $organizacionId = $user->organizacion_seleccionada_id;

            if (! $organizacionId) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No hay organización seleccionada para actualizar.',
                ], 400);
            }

            $esAdmin = $user->organizaciones()
                ->where('organizacion_id', $organizacionId)
                ->wherePivot('es_admin', true)
                ->exists();

            if (! $esAdmin) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tienes permisos para actualizar esta organización.',
                ], 403);
            }

            $organizacion = $user->organizaciones()
                ->where('organizacion_id', $organizacionId)
                ->first();

            if (! $organizacion) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Organización no encontrada.',
                ], 404);
            }

            $organizacion->nombre = $request->string('organizacion_nombre')->trim();
            $organizacion->save();
            $organizacionActualizada = $organizacion;
        }

        if ($actualizoUsuario) {
            $user->save();
        }

        return response()->json([
            'ok' => true,
            'usuario' => $user,
            'organizacion' => $organizacionActualizada,
        ]);
    }
}
