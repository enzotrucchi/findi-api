<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\OrganizacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\DTOs\Organizacion\OrganizacionDTO;

class OrganizacionController extends Controller
{
    public function __construct(private OrganizacionService $organizacionService) {}

    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $soloActivos = (bool) $request->query('activos', false);
            $organizaciones = $this->organizacionService->obtenerColeccion($soloActivos);
            $datos = $organizaciones->map(fn($dto) => $dto->aArray());

            return ApiResponse::exito($datos, 'Organizaciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener organizaciones: ' . $e->getMessage(), 500);
        }
    }

    public function obtener(int $id): JsonResponse
    {
        try {
            $organizacion = $this->organizacionService->obtenerPorId($id);

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito($organizacion->aArray(), 'Organización obtenida exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener organización: ' . $e->getMessage(), 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        try {
            $dto = OrganizacionDTO::desdeArray($request->all());
            $organizacion = $this->organizacionService->crear($dto);

            return ApiResponse::creado($organizacion->aArray(), 'Organización creada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear organización: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        try {
            $dto = OrganizacionDTO::desdeArray($request->all());
            $organizacion = $this->organizacionService->actualizar($id, $dto);

            if (!$organizacion) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito($organizacion->aArray(), 'Organización actualizada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar organización: ' . $e->getMessage(), 500);
        }
    }

    public function eliminar(int $id): JsonResponse
    {
        try {
            $eliminado = $this->organizacionService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Organización no encontrada');
            }

            return ApiResponse::exito(null, 'Organización eliminada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar organización: ' . $e->getMessage(), 500);
        }
    }

    public function buscar(Request $request): JsonResponse
    {
        try {
            $termino = $request->query('q') ?? $request->query('busqueda');

            if (!$termino) {
                return ApiResponse::error('Falta término de búsqueda', 400);
            }

            $resultados = $this->organizacionService->buscar($termino)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($resultados, 'Búsqueda completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error en la búsqueda: ' . $e->getMessage(), 500);
        }
    }

    public function contar(Request $request): JsonResponse
    {
        try {
            $soloActivos = (bool) $request->query('activos', false);
            $cantidad = $this->organizacionService->contar($soloActivos);
            return ApiResponse::exito(['cantidad' => $cantidad], 'Conteo realizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al contar organizaciones: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerPorIds(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $coleccion = $this->organizacionService->obtenerPorIds((array) $ids)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($coleccion, 'Organizaciones obtenidas por ids');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener por ids: ' . $e->getMessage(), 500);
        }
    }

    public function existe(int $id): JsonResponse
    {
        try {
            $existe = $this->organizacionService->existePorId($id);
            return ApiResponse::exito(['existe' => $existe], 'Verificación completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al verificar existencia: ' . $e->getMessage(), 500);
        }
    }
}
