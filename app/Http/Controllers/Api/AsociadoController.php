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

class AsociadoController extends Controller
{
    public function __construct(private AsociadoService $asociadoService) {}

    /**
     * Obtener colección de asociados (paginada) de la organización seleccionada.
     *
     * @return LengthAwarePaginator
     */
    public function obtenerColeccion(FiltroAsociadoDTO $filtroDTO): JsonResponse
    {
        try {
            // Por ahora el DTO está vacío, pero acá podrías mapear query params:
            // $filtroDTO = new FiltroAsociadoDTO(
            //     soloActivos: (bool) $request->query('solo_activos', false),
            //     soloAdmins: (bool) $request->query('solo_admins', false),
            // );
            $filtroDTO = new FiltroAsociadoDTO();

            $asociados = $this->asociadoService->obtenerColeccion($filtroDTO);

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

    public function crear(AsociadoRequest $request): JsonResponse
    {
        try {
            $dto = AsociadoDTO::desdeArray($request->validated());

            $asociado = $this->asociadoService->crear($dto);

            return ApiResponse::creado($asociado, 'Asociado creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
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
}
