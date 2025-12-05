<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Movimiento\ActualizarMovimientoDTO;
use App\DTOs\Movimiento\CrearMovimientoDTO;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\MovimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MovimientoController extends Controller
{
    public function __construct(private MovimientoService $movimientoService) {}

    public function obtenerColeccion(Request $request): JsonResponse
    {
        try {
            $filtros = $request->all();
            $movimientos = $this->movimientoService->obtenerColeccion($filtros);
            $datos = $movimientos->map(fn($dto) => $dto->aArray());

            return ApiResponse::exito($datos, 'Movimientos obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener movimientos: ' . $e->getMessage(), 500);
        }
    }

    public function obtener(int $id): JsonResponse
    {
        try {
            $movimiento = $this->movimientoService->obtenerPorId($id);

            if (!$movimiento) {
                return ApiResponse::noEncontrado('Movimiento no encontrado');
            }

            return ApiResponse::exito($movimiento->aArray(), 'Movimiento obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener movimiento: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerBalance(Request $request): JsonResponse
    {
        try {
            $balance = $this->movimientoService->obtenerBalance();

            return ApiResponse::exito($balance, 'Balance obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener balance: ' . $e->getMessage(), 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        try {
            $dto = CrearMovimientoDTO::desdeArray($request->all());
            $movimiento = $this->movimientoService->crear($dto);

            return ApiResponse::creado($movimiento->aArray(), 'Movimiento creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear movimiento: ' . $e->getMessage(), 500);
        }
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        try {
            $dto = ActualizarMovimientoDTO::desdeArray($request->all());
            $movimiento = $this->movimientoService->actualizar($id, $dto);

            if (!$movimiento) {
                return ApiResponse::noEncontrado('Movimiento no encontrado');
            }

            return ApiResponse::exito($movimiento->aArray(), 'Movimiento actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar movimiento: ' . $e->getMessage(), 500);
        }
    }

    public function eliminar(int $id): JsonResponse
    {
        try {
            $eliminado = $this->movimientoService->eliminar($id);

            if (!$eliminado) {
                return ApiResponse::noEncontrado('Movimiento no encontrado');
            }

            return ApiResponse::exito(null, 'Movimiento eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar movimiento: ' . $e->getMessage(), 500);
        }
    }

    public function buscar(Request $request): JsonResponse
    {
        try {
            $termino = $request->query('q') ?? $request->query('busqueda');

            if (!$termino) {
                return ApiResponse::error('Falta término de búsqueda', 400);
            }

            $resultados = $this->movimientoService->buscar($termino)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($resultados, 'Búsqueda completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error en la búsqueda: ' . $e->getMessage(), 500);
        }
    }

    public function contar(Request $request): JsonResponse
    {
        try {
            $filtros = $request->all();
            $cantidad = $this->movimientoService->contar($filtros);
            return ApiResponse::exito(['cantidad' => $cantidad], 'Conteo realizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al contar movimientos: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerPorIds(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $coleccion = $this->movimientoService->obtenerPorIds((array) $ids)->map(fn($dto) => $dto->aArray());
            return ApiResponse::exito($coleccion, 'Movimientos obtenidos por ids');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener por ids: ' . $e->getMessage(), 500);
        }
    }

    public function existe(int $id): JsonResponse
    {
        try {
            $existe = $this->movimientoService->existePorId($id);
            return ApiResponse::exito(['existe' => $existe], 'Verificación completada');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al verificar existencia: ' . $e->getMessage(), 500);
        }
    }
}
