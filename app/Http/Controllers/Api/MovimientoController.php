<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Movimiento\MovimientoDTO;
use App\DTOs\Movimiento\FiltroMovimientoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Movimiento\MovimientoRequest;
use App\Http\Responses\ApiResponse;
use App\Services\MovimientoService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use App\Http\Requests\Movimiento\MovimientoMasivoRequest;

class MovimientoController extends Controller
{
    public function __construct(private MovimientoService $movimientoService) {}

    /**
     * Obtener colección paginada de movimientos.
     */
    public function obtenerColeccion(): JsonResponse
    {
        try {
            $filtroDTO = new FiltroMovimientoDTO();
            $filtroDTO->setPagina(request()->query('pagina', 1));
            $filtroDTO->setFechaDesde(request()->query('fecha_desde'));
            $filtroDTO->setFechaHasta(request()->query('fecha_hasta'));
            $filtroDTO->setTipo(request()->query('tipo'));

            $movimientos = $this->movimientoService->obtenerColeccion($filtroDTO);

            return ApiResponse::exito($movimientos, 'Movimientos obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener movimientos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener balance (sum de ingresos y egresos).
     */
    public function obtenerBalance(): JsonResponse
    {
        try {
            $balance = $this->movimientoService->obtenerBalance();
            return ApiResponse::exito($balance, 'Balance obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener balance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear un nuevo movimiento.
     */
    public function crear(MovimientoRequest $request): JsonResponse
    {
        try {
            $dto = MovimientoDTO::desdeArray($request->validated());
            $movimiento = $this->movimientoService->crear($dto);

            return ApiResponse::creado($movimiento, 'Movimiento creado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear movimiento: ' . $e->getMessage(), 500);
        }
    }

    public function cargaMasiva(MovimientoMasivoRequest $request): JsonResponse
    {
        try {
            $dtos = [];
            foreach ($request->validated()['movimientos'] as $movimientoData) {
                $dtos[] = MovimientoDTO::desdeArray($movimientoData);
            }

            $movimientos = $this->movimientoService->cargaMasiva($dtos);

            return ApiResponse::creado($movimientos, 'Movimientos creados exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear movimientos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar un movimiento existente.
     */
    public function actualizar(int $id, MovimientoRequest $request): JsonResponse
    {
        try {
            $dto = MovimientoDTO::desdeArray($request->validated());
            $movimiento = $this->movimientoService->actualizar($id, $dto);

            if (!$movimiento) {
                return ApiResponse::noEncontrado('Movimiento no encontrado');
            }

            return ApiResponse::exito($movimiento, 'Movimiento actualizado exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar movimiento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar un movimiento.
     */
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

    /**
     * Descargar comprobante en PDF de un movimiento.
     */
    public function descargarComprobante(int $id)
    {
        try {
            $pdf = $this->movimientoService->descargarComprobante($id);

            if (!$pdf) {
                return ApiResponse::noEncontrado('Movimiento no encontrado');
            }

            return $pdf;
        } catch (\Exception $e) {
            return ApiResponse::error('Error al generar comprobante: ' . $e->getMessage(), 500);
        }
    }
}
