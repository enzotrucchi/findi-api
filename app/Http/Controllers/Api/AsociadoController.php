<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Asociado\ActualizarAsociadoDTO;
use App\DTOs\Asociado\CrearAsociadoDTO;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\AsociadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AsociadoController extends Controller
{
    public function __construct(private AsociadoService $asociadoService) {}

    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $soloActivos = (bool) $request->query('activos', false);
            $soloAdmins = (bool) $request->query('solo_admins', false);

            $asociados = $this->asociadoService->obtenerColeccion($soloActivos, $soloAdmins);
            $datos = $asociados->map(fn($dto) => $dto->aArray());

            return ApiResponse::exito($datos, 'Asociados obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener asociados: ' . $e->getMessage(), 500);
        }
    }

    public function obtener(int $id): JsonResponse
    {
        try {
            $asociado = $this->asociadoService->obtenerPorId($id);

            if (!$asociado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($asociado->aArray(), 'Asociado obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener asociado: ' . $e->getMessage(), 500);
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

    public function crear(Request $request): JsonResponse
    {
        try {
            $dto = CrearAsociadoDTO::desdeArray($request->all());
            $asociado = $this->asociadoService->crear($dto);

            return ApiResponse::creado($asociado->aArray(), 'Asociado creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear asociado: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        try {
            $dto = ActualizarAsociadoDTO::desdeArray($request->all());
            $asociado = $this->asociadoService->actualizar($id, $dto);

            if (!$asociado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito($asociado->aArray(), 'Asociado actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar asociado: ' . $e->getMessage(), 500);
        }
    }

    public function eliminar(int $id): JsonResponse
    {
        try {
            $eliminado = $this->asociadoService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Asociado no encontrado');
            }

            return ApiResponse::exito(null, 'Asociado eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar asociado: ' . $e->getMessage(), 500);
        }
    }

    public function buscar(Request $request): JsonResponse
    {
        try {
            $termino = $request->query('q') ?? $request->query('busqueda');

            if (!$termino) {
                return ApiResponse::error('Falta término de búsqueda', 400);
            }

            $resultados = $this->asociadoService->buscar($termino)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($resultados, 'Búsqueda completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error en la búsqueda: ' . $e->getMessage(), 500);
        }
    }

    public function contar(Request $request): JsonResponse
    {
        try {
            $soloActivos = (bool) $request->query('activos', false);
            $cantidad = $this->asociadoService->contar($soloActivos);
            return ApiResponse::exito(['cantidad' => $cantidad], 'Conteo realizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al contar asociados: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerPorIds(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $coleccion = $this->asociadoService->obtenerPorIds((array) $ids)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($coleccion, 'Asociados obtenidos por ids');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener por ids: ' . $e->getMessage(), 500);
        }
    }

    public function existe(int $id): JsonResponse
    {
        try {
            $existe = $this->asociadoService->existePorId($id);
            return ApiResponse::exito(['existe' => $existe], 'Verificación completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al verificar existencia: ' . $e->getMessage(), 500);
        }
    }
}
