<?php

namespace App\DTOs\Movimiento;

/**
 * DTO para Movimiento
 * 
 * Encapsula los datos necesarios para crear/actualizar un movimiento.
 */
class MovimientoDTO
{
    public function __construct(
        public readonly string $fecha,
        public readonly string $tipo,
        public readonly float $monto,
        public readonly ?string $hora = null,
        public readonly ?string $detalle = null,
        public readonly ?string $status = null,
        public readonly ?string $adjunto = null,
        public readonly ?int $proyectoId = null,
        public readonly ?int $asociadoId = null,
        public readonly ?int $proveedorId = null,
        public readonly ?int $modoPagoId = null,
    ) {}

    /**
     * Crear DTO desde un array de datos.
     *
     * @param array<string, mixed> $datos
     * @return self
     */
    public static function desdeArray(array $datos): self
    {
        return new self(
            fecha: $datos['fecha'],
            tipo: $datos['tipo'],
            monto: (float) $datos['monto'],
            hora: $datos['hora'] ?? null,
            detalle: $datos['detalle'] ?? null,
            status: $datos['status'] ?? null,
            adjunto: $datos['adjunto'] ?? null,
            proyectoId: isset($datos['proyecto_id']) ? (int) $datos['proyecto_id'] : null,
            asociadoId: isset($datos['asociado_id']) ? (int) $datos['asociado_id'] : null,
            proveedorId: isset($datos['proveedor_id']) ? (int) $datos['proveedor_id'] : null,
            modoPagoId: isset($datos['modo_pago_id']) ? (int) $datos['modo_pago_id'] : null,
        );
    }

    /**
     * Convertir DTO a array para almacenamiento.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        return [
            'fecha' => $this->fecha,
            'tipo' => $this->tipo,
            'monto' => $this->monto,
            'hora' => $this->hora,
            'detalle' => $this->detalle,
            'status' => $this->status,
            'adjunto' => $this->adjunto,
            'proyecto_id' => $this->proyectoId,
            'asociado_id' => $this->asociadoId,
            'proveedor_id' => $this->proveedorId,
            'modo_pago_id' => $this->modoPagoId,
        ];
    }
}
