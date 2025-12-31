<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Organizacion;
use App\Services\FacturaService;
use App\DTOs\Factura\FacturaDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacturaController extends Controller
{
    public function __construct(
        private FacturaService $facturaService
    ) {}

    /**
     * Listar facturas de una organización.
     *
     * @param int $organizacionId
     * @return JsonResponse
     */
    public function index(int $organizacionId): JsonResponse
    {
        $organizacion = Organizacion::findOrFail($organizacionId);

        $facturas = $this->facturaService->obtenerHistorialFacturas($organizacion);

        return response()->json([
            'data' => $facturas->map(fn($factura) => $this->transformarFactura($factura)),
        ]);
    }

    /**
     * Obtener facturas pendientes de una organización.
     *
     * @param int $organizacionId
     * @return JsonResponse
     */
    public function pendientes(int $organizacionId): JsonResponse
    {
        $organizacion = Organizacion::findOrFail($organizacionId);

        $facturas = $this->facturaService->obtenerFacturasPendientes($organizacion);

        return response()->json([
            'data' => $facturas->map(fn($factura) => $this->transformarFactura($factura)),
        ]);
    }

    /**
     * Obtener una factura específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $factura = Factura::with('organizacion')->findOrFail($id);

        return response()->json([
            'data' => $this->transformarFactura($factura),
        ]);
    }

    /**
     * Generar una nueva factura para una organización.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organizacion_id' => 'required|exists:organizaciones,id',
            'periodo' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
            'precio_unitario' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $organizacion = Organizacion::findOrFail($request->organizacion_id);

            $factura = $this->facturaService->generarFactura(
                $organizacion,
                $request->periodo,
                $request->precio_unitario
            );

            return response()->json([
                'message' => 'Factura generada exitosamente',
                'data' => $this->transformarFactura($factura),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar factura',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Procesar el pago de una factura.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function procesarPago(int $id): JsonResponse
    {
        $factura = Factura::findOrFail($id);

        if ($factura->estaPagada()) {
            return response()->json([
                'message' => 'La factura ya fue pagada',
            ], 400);
        }

        try {
            $this->facturaService->procesarPago($factura);

            return response()->json([
                'message' => 'Pago procesado exitosamente',
                'data' => $this->transformarFactura($factura->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar pago',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancelar una factura.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelar(int $id, Request $request): JsonResponse
    {
        $factura = Factura::findOrFail($id);

        try {
            $motivo = $request->input('motivo');
            $this->facturaService->cancelarFactura($factura, $motivo);

            return response()->json([
                'message' => 'Factura cancelada exitosamente',
                'data' => $this->transformarFactura($factura->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cancelar factura',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener estadísticas de facturas.
     *
     * @param int $organizacionId
     * @return JsonResponse
     */
    public function estadisticas(int $organizacionId): JsonResponse
    {
        $organizacion = Organizacion::findOrFail($organizacionId);

        $facturas = $organizacion->facturas;

        $estadisticas = [
            'total_facturas' => $facturas->count(),
            'facturas_pendientes' => $facturas->where('estado', Factura::ESTADO_PENDING)->count(),
            'facturas_pagadas' => $facturas->where('estado', Factura::ESTADO_PAID)->count(),
            'facturas_vencidas' => $facturas->where('estado', Factura::ESTADO_EXPIRED)->count(),
            'facturas_canceladas' => $facturas->where('estado', Factura::ESTADO_CANCELLED)->count(),
            'monto_total_pendiente' => $facturas->where('estado', Factura::ESTADO_PENDING)->sum('monto_total'),
            'monto_total_pagado' => $facturas->where('estado', Factura::ESTADO_PAID)->sum('monto_total'),
        ];

        return response()->json([
            'data' => $estadisticas,
        ]);
    }

    /**
     * Transformar factura para la respuesta.
     *
     * @param Factura $factura
     * @return array
     */
    private function transformarFactura(Factura $factura): array
    {
        return [
            'id' => $factura->id,
            'organizacion_id' => $factura->organizacion_id,
            'organizacion_nombre' => $factura->organizacion->nombre ?? null,
            'periodo' => $factura->periodo,
            'fecha_corte' => $factura->fecha_corte->format('Y-m-d'),
            'cantidad_asociados' => $factura->cantidad_asociados,
            'precio_unitario' => (float) $factura->precio_unitario,
            'monto_total' => (float) $factura->monto_total,
            'fecha_vencimiento' => $factura->fecha_vencimiento->format('Y-m-d'),
            'estado' => $factura->estado,
            'fecha_pago' => $factura->fecha_pago?->format('Y-m-d H:i:s'),
            'esta_pagada' => $factura->estaPagada(),
            'esta_vencida' => $factura->estaVencida(),
            'esta_pendiente' => $factura->estaPendiente(),
            'created_at' => $factura->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $factura->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
