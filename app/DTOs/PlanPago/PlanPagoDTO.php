<?php

namespace App\DTOs\PlanPago;

class PlanPagoDTO
{
    /**
     * @param array<int> $asociadoIds
     * @param array<CuotaDTO> $cuotas
     */
    public function __construct(
        public readonly array $asociadoIds,
        public readonly string $descripcion,
        public readonly int $cantidadCuotas,
        public readonly string $fechaPrimerVencimiento,
        public readonly int $frecuenciaMensual = 1,
        public readonly ?float $importeTotal = null,
        public readonly ?float $importePorCuota = null,
        public readonly array $cuotas = [],
        public readonly ?int $organizacionId = null,
    ) {}

    /**
     * @param array<string, mixed> $datos
     */
    public static function desdeArray(array $datos): self
    {
        $asociadoIds = isset($datos['asociado_ids']) && is_array($datos['asociado_ids'])
            ? array_map('intval', $datos['asociado_ids'])
            : [isset($datos['asociado_id']) ? (int) $datos['asociado_id'] : 0];

        $cuotas = [];
        if (isset($datos['cuotas']) && is_array($datos['cuotas'])) {
            $cuotas = array_map(
                static fn(array $cuota): CuotaDTO => CuotaDTO::desdeArray($cuota),
                $datos['cuotas']
            );
        }

        return new self(
            asociadoIds: array_values(array_filter($asociadoIds, static fn(int $id): bool => $id > 0)),
            descripcion: trim($datos['descripcion']),
            cantidadCuotas: (int) $datos['cantidad_cuotas'],
            fechaPrimerVencimiento: $datos['fecha_primer_vencimiento'],
            frecuenciaMensual: isset($datos['frecuencia_mensual']) ? (int) $datos['frecuencia_mensual'] : 1,
            importeTotal: isset($datos['importe_total']) ? (float) $datos['importe_total'] : null,
            importePorCuota: isset($datos['importe_por_cuota']) ? (float) $datos['importe_por_cuota'] : null,
            cuotas: $cuotas,
            organizacionId: isset($datos['organizacion_id']) ? (int) $datos['organizacion_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        return [
            'asociado_ids' => $this->asociadoIds,
            'descripcion' => $this->descripcion,
            'cantidad_cuotas' => $this->cantidadCuotas,
            'fecha_primer_vencimiento' => $this->fechaPrimerVencimiento,
            'frecuencia_mensual' => $this->frecuenciaMensual,
            'importe_total' => $this->importeTotal,
            'importe_por_cuota' => $this->importePorCuota,
            'cuotas' => array_map(
                static fn(CuotaDTO $cuota): array => $cuota->aArray(),
                $this->cuotas
            ),
            'organizacion_id' => $this->organizacionId,
        ];
    }
}
