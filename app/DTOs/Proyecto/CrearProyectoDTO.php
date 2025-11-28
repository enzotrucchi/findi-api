<?php

namespace App\DTOs\Proyecto;

/**
 * DTO para creación de Proyecto
 * 
 * Encapsula los datos necesarios para crear un nuevo proyecto.
 */
class CrearProyectoDTO
{
    /**
     * Constructor.
     *
     * @param string $descripcion
     * @param float $montoObjetivo
     * @param string $fechaAlta
     * @param float $montoActual
     * @param string|null $fechaRealizacion
     */
    public function __construct(
        public readonly string $descripcion,
        public readonly float $montoObjetivo,
        public readonly string $fechaAlta,
        public readonly float $montoActual = 0,
        public readonly ?string $fechaRealizacion = null,
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
            descripcion: $datos['descripcion'],
            montoObjetivo: (float) $datos['monto_objetivo'],
            fechaAlta: $datos['fecha_alta'],
            montoActual: isset($datos['monto_actual']) ? (float) $datos['monto_actual'] : 0,
            fechaRealizacion: $datos['fecha_realizacion'] ?? null,
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
            'descripcion' => $this->descripcion,
            'monto_actual' => $this->montoActual,
            'monto_objetivo' => $this->montoObjetivo,
            'fecha_alta' => $this->fechaAlta,
            'fecha_realizacion' => $this->fechaRealizacion,
        ];
    }
}
