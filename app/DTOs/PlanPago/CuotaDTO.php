<?php

namespace App\DTOs\PlanPago;

class CuotaDTO
{
    public function __construct(
        public readonly int $numero,
        public readonly float $importe,
        public readonly string $fechaVencimiento,
        public readonly string $estado = 'pendiente',
    ) {}

    /**
     * @param array<string, mixed> $datos
     */
    public static function desdeArray(array $datos): self
    {
        return new self(
            numero: (int) $datos['numero'],
            importe: (float) $datos['importe'],
            fechaVencimiento: $datos['fecha_vencimiento'],
            estado: $datos['estado'] ?? 'pendiente',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        return [
            'numero' => $this->numero,
            'importe' => $this->importe,
            'fecha_vencimiento' => $this->fechaVencimiento,
            'estado' => $this->estado,
        ];
    }
}
