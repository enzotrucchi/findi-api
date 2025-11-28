<?php

namespace App\DTOs\Movimiento;

/**
 * DTO para actualización de Movimiento
 * 
 * Encapsula los datos necesarios para actualizar un movimiento existente.
 */
class ActualizarMovimientoDTO
{
    /**
     * Constructor.
     *
     * @param string|null $fecha
     * @param string|null $hora
     * @param string|null $detalle
     * @param float|null $monto
     * @param string|null $tipo
     * @param string|null $status
     * @param string|null $adjunto
     * @param int|null $proyectoId
     * @param int|null $asociadoId
     * @param int|null $proveedorId
     * @param int|null $modoPagoId
     * @param int|null $organizacionId
     */
    public function __construct(
        public readonly ?string $fecha = null,
        public readonly ?string $hora = null,
        public readonly ?string $detalle = null,
        public readonly ?float $monto = null,
        public readonly ?string $tipo = null,
        public readonly ?string $status = null,
        public readonly ?string $adjunto = null,
        public readonly ?int $proyectoId = null,
        public readonly ?int $asociadoId = null,
        public readonly ?int $proveedorId = null,
        public readonly ?int $modoPagoId = null,
        public readonly ?int $organizacionId = null,
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
            fecha: $datos['fecha'] ?? null,
            hora: $datos['hora'] ?? null,
            detalle: $datos['detalle'] ?? null,
            monto: isset($datos['monto']) ? (float) $datos['monto'] : null,
            tipo: $datos['tipo'] ?? null,
            status: $datos['status'] ?? null,
            adjunto: array_key_exists('adjunto', $datos) ? $datos['adjunto'] : null,
            proyectoId: isset($datos['proyecto_id']) ? (int) $datos['proyecto_id'] : null,
            asociadoId: isset($datos['asociado_id']) ? (int) $datos['asociado_id'] : null,
            proveedorId: isset($datos['proveedor_id']) ? (int) $datos['proveedor_id'] : null,
            modoPagoId: isset($datos['modo_pago_id']) ? (int) $datos['modo_pago_id'] : null,
            organizacionId: isset($datos['organizacion_id']) ? (int) $datos['organizacion_id'] : null,
        );
    }

    /**
     * Convertir DTO a array para almacenamiento.
     * Solo incluye los campos que no son null.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        $datos = [];

        if ($this->fecha !== null) {
            $datos['fecha'] = $this->fecha;
        }

        if ($this->hora !== null) {
            $datos['hora'] = $this->hora;
        }

        if ($this->detalle !== null) {
            $datos['detalle'] = $this->detalle;
        }

        if ($this->monto !== null) {
            $datos['monto'] = $this->monto;
        }

        if ($this->tipo !== null) {
            $datos['tipo'] = $this->tipo;
        }

        if ($this->status !== null) {
            $datos['status'] = $this->status;
        }

        if ($this->adjunto !== null) {
            $datos['adjunto'] = $this->adjunto;
        }

        if ($this->proyectoId !== null) {
            $datos['proyecto_id'] = $this->proyectoId;
        }

        if ($this->asociadoId !== null) {
            $datos['asociado_id'] = $this->asociadoId;
        }

        if ($this->proveedorId !== null) {
            $datos['proveedor_id'] = $this->proveedorId;
        }

        if ($this->modoPagoId !== null) {
            $datos['modo_pago_id'] = $this->modoPagoId;
        }

        if ($this->organizacionId !== null) {
            $datos['organizacion_id'] = $this->organizacionId;
        }

        return $datos;
    }
}
