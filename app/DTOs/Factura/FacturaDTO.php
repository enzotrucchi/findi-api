<?php

namespace App\DTOs\Factura;

/**
 * DTO para Factura
 * 
 * Encapsula los datos necesarios para crear/actualizar una factura.
 */
class FacturaDTO
{
    public function __construct(
        public readonly int $organizacionId,
        public readonly string $periodo,
        public readonly string $fechaCorte,
        public readonly int $cantidadAsociados,
        public readonly float $precioUnitario,
        public readonly float $montoTotal,
        public readonly string $fechaVencimiento,
        public readonly ?string $estado = null,
        public readonly ?string $fechaPago = null,
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
            organizacionId: (int) $datos['organizacion_id'],
            periodo: $datos['periodo'],
            fechaCorte: $datos['fecha_corte'],
            cantidadAsociados: (int) $datos['cantidad_asociados'],
            precioUnitario: (float) $datos['precio_unitario'],
            montoTotal: (float) $datos['monto_total'],
            fechaVencimiento: $datos['fecha_vencimiento'],
            estado: $datos['estado'] ?? null,
            fechaPago: $datos['fecha_pago'] ?? null,
        );
    }

    /**
     * Convertir DTO a array para almacenamiento.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        $data = [
            'organizacion_id' => $this->organizacionId,
            'periodo' => $this->periodo,
            'fecha_corte' => $this->fechaCorte,
            'cantidad_asociados' => $this->cantidadAsociados,
            'precio_unitario' => $this->precioUnitario,
            'monto_total' => $this->montoTotal,
            'fecha_vencimiento' => $this->fechaVencimiento,
        ];

        if ($this->estado !== null) {
            $data['estado'] = $this->estado;
        }

        if ($this->fechaPago !== null) {
            $data['fecha_pago'] = $this->fechaPago;
        }

        return $data;
    }

    /**
     * Crear DTO desde un modelo Factura.
     *
     * @param \App\Models\Factura $factura
     * @return self
     */
    public static function desdeModelo($factura): self
    {
        return new self(
            organizacionId: $factura->organizacion_id,
            periodo: $factura->periodo,
            fechaCorte: $factura->fecha_corte->format('Y-m-d'),
            cantidadAsociados: $factura->cantidad_asociados,
            precioUnitario: (float) $factura->precio_unitario,
            montoTotal: (float) $factura->monto_total,
            fechaVencimiento: $factura->fecha_vencimiento->format('Y-m-d'),
            estado: $factura->estado,
            fechaPago: $factura->fecha_pago?->format('Y-m-d H:i:s'),
        );
    }
}
