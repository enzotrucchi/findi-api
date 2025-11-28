<?php

namespace App\DTOs\Movimiento;

/**
 * DTO para creación de Movimiento
 * 
 * Encapsula los datos necesarios para crear un nuevo movimiento.
 */
class CrearMovimientoDTO
{
    /**
     * Constructor.
     *
     * @param string $fecha
     * @param string $hora
     * @param string $detalle
     * @param float $monto
     * @param string $tipo
     * @param int $organizacionId
     * @param string $status
     * @param string|null $adjunto
     * @param int|null $proyectoId
     * @param int|null $asociadoId
     * @param int|null $proveedorId
     * @param int|null $modoPagoId
     */
    public function __construct(
        public readonly string $fecha,
        public readonly string $hora,
        public readonly string $detalle,
        public readonly float $monto,
        public readonly string $tipo,
        public readonly int $organizacionId,
        public readonly string $status = 'pendiente',
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
            hora: $datos['hora'],
            detalle: $datos['detalle'],
            monto: (float) $datos['monto'],
            tipo: $datos['tipo'],
            organizacionId: (int) $datos['organizacion_id'],
            status: $datos['status'] ?? 'pendiente',
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
            'hora' => $this->hora,
            'detalle' => $this->detalle,
            'monto' => $this->monto,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'adjunto' => $this->adjunto,
            'proyecto_id' => $this->proyectoId,
            'asociado_id' => $this->asociadoId,
            'proveedor_id' => $this->proveedorId,
            'modo_pago_id' => $this->modoPagoId,
            'organizacion_id' => $this->organizacionId,
        ];
    }
}
