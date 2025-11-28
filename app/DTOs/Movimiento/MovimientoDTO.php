<?php

namespace App\DTOs\Movimiento;

use App\Models\Movimiento;

/**
 * DTO para Movimiento
 * 
 * Objeto de transferencia de datos inmutable para representar
 * un movimiento en las respuestas de la API.
 */
class MovimientoDTO
{
    /**
     * Constructor privado para forzar el uso de métodos factory.
     */
    private function __construct(
        public readonly int $id,
        public readonly string $fecha,
        public readonly string $hora,
        public readonly string $detalle,
        public readonly float $monto,
        public readonly string $tipo,
        public readonly string $status,
        public readonly ?string $adjunto,
        public readonly ?int $proyectoId,
        public readonly ?int $asociadoId,
        public readonly ?int $proveedorId,
        public readonly ?int $modoPagoId,
        public readonly int $organizacionId,
        public readonly string $fechaCreacion,
        public readonly string $fechaActualizacion,
    ) {}

    /**
     * Crear DTO desde un modelo Eloquent.
     *
     * @param Movimiento $movimiento
     * @return self
     */
    public static function desdeModelo(Movimiento $movimiento): self
    {
        return new self(
            id: $movimiento->id,
            fecha: $movimiento->fecha,
            hora: $movimiento->hora,
            detalle: $movimiento->detalle,
            monto: $movimiento->monto,
            tipo: $movimiento->tipo,
            status: $movimiento->status,
            adjunto: $movimiento->adjunto,
            proyectoId: $movimiento->proyecto_id,
            asociadoId: $movimiento->asociado_id,
            proveedorId: $movimiento->proveedor_id,
            modoPagoId: $movimiento->modo_pago_id,
            organizacionId: $movimiento->organizacion_id,
            fechaCreacion: $movimiento->created_at->toIso8601String(),
            fechaActualizacion: $movimiento->updated_at->toIso8601String(),
        );
    }

    /**
     * Convertir DTO a array.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'detalle' => $this->detalle,
            'monto' => $this->monto,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'adjunto' => $this->adjunto,
            'proyectoId' => $this->proyectoId,
            'asociadoId' => $this->asociadoId,
            'proveedorId' => $this->proveedorId,
            'modoPagoId' => $this->modoPagoId,
            'organizacionId' => $this->organizacionId,
            'fechaCreacion' => $this->fechaCreacion,
            'fechaActualizacion' => $this->fechaActualizacion,
        ];
    }
}
