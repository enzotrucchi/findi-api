<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Proyecto\FiltroProyectoDTO;
use App\DTOs\Proyecto\ProyectoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proyecto\ProyectoRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ProyectoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use App\Models\Proyecto;

class ProyectoController extends Controller
{
    public function __construct(private ProyectoService $proyectoService) {}

    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $filtroDTO = new FiltroProyectoDTO();
            // $filtroDTO->setPagina(request()->input('pagina', 1));

            $proyectos = $this->proyectoService->obtenerColeccion($filtroDTO);

            return ApiResponse::exito($proyectos, 'Proyectos obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener proyectos: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->proyectoService->obtenerEstadisticas();

            return ApiResponse::exito($estadisticas, 'Estadísticas de proyectos obtenidas exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener estadísticas de proyectos: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerMovimientos(int $id): JsonResponse
    {
        try {
            $tipo = request()->query('tipo');
            $movimientos = $this->proyectoService->obtenerMovimientosPorProyecto($id, $tipo);

            return ApiResponse::exito($movimientos, 'Movimientos del proyecto obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener movimientos del proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function crear(ProyectoRequest $request): JsonResponse
    {
        try {
            $dto = ProyectoDTO::desdeArray($request->validated());

            $proyecto = $this->proyectoService->crear($dto);

            return ApiResponse::creado($proyecto, 'Proyecto creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(ProyectoRequest $request, int $id): JsonResponse
    {
        try {
            $dto = ProyectoDTO::desdeArray($request->all());
            $proyecto = $this->proyectoService->actualizar($id, $dto);

            if (!$proyecto) {
                return ApiResponse::noEncontrado('Proyecto no encontrado');
            }

            return ApiResponse::exito($proyecto, 'Proyecto actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar proyecto: ' . $e->getMessage(), 500);
        }
    }

    public function eliminar(int $id): JsonResponse
    {
        try {

            $proyecto = Proyecto::find($id);
            if (!$proyecto) {
                return ApiResponse::noEncontrado('Proyecto no encontrado');
            }

            /**
             * Si un proyecto tiene movimientos asociados, no se debe permitir su eliminación.
             */
            if ($proyecto->movimientos()->exists()) {
                return ApiResponse::error('No se puede eliminar el proyecto porque tiene movimientos asociados.', 400);
            }

            $eliminado = $this->proyectoService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Proyecto no encontrado');
            }

            return ApiResponse::exito(null, 'Proyecto eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar proyecto: ' . $e->getMessage(), 500);
        }
    }
}
