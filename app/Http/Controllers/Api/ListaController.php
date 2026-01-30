<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ListaService;
use App\DTOs\Lista\ListaDTO;
use App\DTOs\Lista\FiltroListaDTO;
use App\Http\Requests\Lista\ListaRequest;
use App\Http\Requests\Lista\AsociadosListaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ListaController extends Controller
{
    public function __construct(private ListaService $listaService) {}

    /**
     * GET /api/listas
     * Obtener colección paginada de listas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filtroDTO = new FiltroListaDTO();
            $filtroDTO->setPagina($request->input('pagina', 1));

            $listas = $this->listaService->obtenerColeccion($filtroDTO);

            return ApiResponse::exito($listas, 'Listas obtenidas exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener listas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/listas/todas
     * Obtener todas las listas sin paginación (para dropdowns/selects)
     */
    public function todas(): JsonResponse
    {
        try {
            $listas = $this->listaService->obtenerTodas();
            return ApiResponse::exito($listas, 'Listas obtenidas exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener listas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/listas/{id}
     * Obtener una lista por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lista = $this->listaService->obtenerPorId($id);

            if (!$lista) {
                return ApiResponse::noEncontrado('Lista no encontrada');
            }

            return ApiResponse::exito($lista, 'Lista obtenida exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener lista: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/listas
     * Crear una nueva lista
     */
    public function store(ListaRequest $request): JsonResponse
    {
        try {
            $dto = ListaDTO::desdeArray($request->validated());
            $lista = $this->listaService->crear($dto);

            return ApiResponse::creado($lista, 'Lista creada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear lista: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/listas/{id}
     * Actualizar una lista existente
     */
    public function update(int $id, ListaRequest $request): JsonResponse
    {
        try {
            $dto = ListaDTO::desdeArray($request->validated());
            $lista = $this->listaService->actualizar($id, $dto);

            return ApiResponse::exito($lista, 'Lista actualizada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar lista: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/listas/{id}
     * Eliminar una lista
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $resultado = $this->listaService->eliminar($id);

            if (!$resultado) {
                return ApiResponse::noEncontrado('Lista no encontrada');
            }

            return ApiResponse::exito(null, 'Lista eliminada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar lista: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/listas/{id}/asociados
     * Obtener asociados de una lista
     */
    public function asociados(int $id): JsonResponse
    {
        try {
            $asociados = $this->listaService->obtenerAsociadosPorLista($id);
            return ApiResponse::exito($asociados, 'Asociados obtenidos exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener asociados: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/listas/{id}/asociados
     * Agregar asociados a una lista
     */
    public function agregarAsociados(int $id, AsociadosListaRequest $request): JsonResponse
    {
        try {
            $lista = $this->listaService->agregarAsociados($id, $request->validated()['asociado_ids']);
            return ApiResponse::exito($lista, 'Asociados agregados exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al agregar asociados: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/listas/{id}/asociados
     * Reemplazar todos los asociados de una lista
     */
    public function reemplazarAsociados(int $id, AsociadosListaRequest $request): JsonResponse
    {
        try {
            $lista = $this->listaService->reemplazarAsociados($id, $request->validated()['asociado_ids']);
            return ApiResponse::exito($lista, 'Asociados actualizados exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar asociados: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/listas/{id}/asociados/{asociadoId}
     * Remover un asociado de una lista
     */
    public function eliminarAsociado(int $id, int $asociadoId): JsonResponse
    {
        try {
            $lista = $this->listaService->eliminarAsociado($id, $asociadoId);
            return ApiResponse::exito($lista, 'Asociado removido exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al remover asociado: ' . $e->getMessage(), 500);
        }
    }
}
