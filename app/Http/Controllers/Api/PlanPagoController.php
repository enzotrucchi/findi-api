<?php

namespace App\Http\Controllers\Api;

use App\DTOs\PlanPago\PlanPagoDTO;
use App\DTOs\PlanPago\FiltroPlanPagoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlanPago\CancelarCuotaRequest;
use App\Http\Requests\PlanPago\GetPlanesPagoRequest;
use App\Http\Requests\PlanPago\StorePlanPagoRequest;
use App\Http\Responses\ApiResponse;
use App\Models\PlanPago;
use App\Services\PlanPagoService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class PlanPagoController extends Controller
{
    public function __construct(private PlanPagoService $planPagoService) {}

    public function crear(StorePlanPagoRequest $request): JsonResponse
    {
        try {
            $dto = PlanPagoDTO::desdeArray($request->validated());
            \App\Jobs\GenerarPlanPagoJob::dispatch($dto->aArray());
            return ApiResponse::creado(null, 'El plan de pago se está generando en segundo plano. Recibirá una notificación al finalizar.');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear plan de pago: ' . $e->getMessage(), 500);
        }
    }

    public function obtener(int $id): JsonResponse
    {
        try {
            $planPago = $this->planPagoService->obtenerPorId($id);

            if (!$planPago) {
                return ApiResponse::noEncontrado('Plan de pago no encontrado');
            }

            return ApiResponse::exito($planPago, 'Plan de pago obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener plan de pago: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerColeccion(GetPlanesPagoRequest $request): JsonResponse
    {
        try {
            $filtroDTO = FiltroPlanPagoDTO::desdeArray($request->validated());
            $planes = $this->planPagoService->obtenerColeccion($filtroDTO);
            return ApiResponse::exito($planes, 'Planes de pago obtenidos exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener planes de pago: ' . $e->getMessage(), 500);
        }
    }

    public function cancelarCuota(int $id, CancelarCuotaRequest $request): JsonResponse
    {
        try {
            $cuota = $this->planPagoService->cancelarCuota($id);
            return ApiResponse::exito($cuota, 'Cuota pagada exitosamente');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al cancelar cuota: ' . $e->getMessage(), 500);
        }
    }
}
