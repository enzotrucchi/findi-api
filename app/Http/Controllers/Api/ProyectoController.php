<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Proyecto\ActualizarProyectoDTO;
use App\DTOs\Proyecto\CrearProyectoDTO;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ProyectoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProyectoController extends Controller
{
    public function __construct(private ProyectoService $proyectoService) {}

    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $soloActivos = (bool) $request->query('activos', false);
            $proyectos = $this->proyectoService->obtenerColeccion($soloActivos);
            $datos = $proyectos->map(fn($dto) => $dto->aArray());

            return ApiResponse::exito($datos, 'Proyectos obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener proyectos: ' . $e->getMessage(), 500);
        }
    }

    public function obtener(int $id): JsonResponse
    {
        try {
            $proyecto = $this->proyectoService->obtenerPorId($id);

            if (!$proyecto) {
                return ApiResponse::noEncontrado('Proyecto no encontrado');
            }

            return ApiResponse::exito($proyecto->aArray(), 'Proyecto obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        try {
            $dto = CrearProyectoDTO::desdeArray($request->all());
            $proyecto = $this->proyectoService->crear($dto);

            return ApiResponse::creado($proyecto->aArray(), 'Proyecto creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        try {
            $dto = ActualizarProyectoDTO::desdeArray($request->all());
            $proyecto = $this->proyectoService->actualizar($id, $dto);

            if (!$proyecto) {
                return ApiResponse::noEncontrado('Proyecto no encontrado');
            }

            return ApiResponse::exito($proyecto->aArray(), 'Proyecto actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function eliminar(int $id): JsonResponse
    {
        try {
            $eliminado = $this->proyectoService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Proyecto no encontrado');
            }

            return ApiResponse::exito(null, 'Proyecto eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function buscar(Request $request): JsonResponse
    {
        try {
            $termino = $request->query('q') ?? $request->query('busqueda');

            if (!$termino) {
                return ApiResponse::error('Falta término de búsqueda', 400);
            }

            $resultados = $this->proyectoService->buscar($termino)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($resultados, 'Búsqueda completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error en la búsqueda: ' . $e->getMessage(), 500);
        }
    }

    public function contar(Request $request): JsonResponse
    {
        try {
            $soloActivos = (bool) $request->query('activos', false);
            $cantidad = $this->proyectoService->contar($soloActivos);
            return ApiResponse::exito(['cantidad' => $cantidad], 'Conteo realizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al contar proyectos: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerPorIds(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $coleccion = $this->proyectoService->obtenerPorIds((array) $ids)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($coleccion, 'Proyectos obtenidos por ids');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener por ids: ' . $e->getMessage(), 500);
        }
    }

    public function existe(int $id): JsonResponse
    {
        try {
            $existe = $this->proyectoService->existePorId($id);
            return ApiResponse::exito(['existe' => $existe], 'Verificación completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al verificar existencia: ' . $e->getMessage(), 500);
        }
    }
}
