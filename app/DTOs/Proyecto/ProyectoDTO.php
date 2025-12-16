<?php

namespace App\DTOs\Proyecto;

use App\Models\Proyecto;

/**
 * DTO para Proyecto
 * 
 * Objeto de transferencia de datos inmutable para representar
 * un proyecto en las respuestas de la API.
 */
class ProyectoDTO
{
    /**
     * Constructor privado para forzar el uso de métodos factory.
     */
    private function __construct(
        public readonly int $id,
        public readonly string $descripcion,
        public readonly ?float $montoActual = null,
        public readonly float $montoObjetivo,
        public readonly string $fechaAlta,
        public readonly ?string $fechaRealizacion,
        public readonly string $fechaCreacion,
        public readonly string $fechaActualizacion,
    ) {}

    public static function desdeArray(array $datos): self
    {
        return new self(
            id: $datos['id'] ?? 0,
            descripcion: $datos['descripcion'],
            montoActual: $datos['monto_actual'] ?? null,
            montoObjetivo: $datos['monto_objetivo'],
            fechaAlta: $datos['fecha_alta'],
            fechaRealizacion: $datos['fecha_realizacion'] ?? null,
            fechaCreacion: $datos['fecha_creacion'] ?? now()->toIso8601String(),
            fechaActualizacion: $datos['fecha_actualizacion'] ?? now()->toIso8601String(),
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
            'descripcion' => $this->descripcion,
            'montoActual' => $this->montoActual,
            'montoObjetivo' => $this->montoObjetivo,
            'fechaAlta' => $this->fechaAlta,
            'fechaRealizacion' => $this->fechaRealizacion,
            'porcentajeAvance' => $this->montoObjetivo > 0
                ? round(($this->montoActual / $this->montoObjetivo) * 100, 2)
                : 0,
            'fechaCreacion' => $this->fechaCreacion,
            'fechaActualizacion' => $this->fechaActualizacion,
        ];
    }
}
