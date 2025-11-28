<?php

namespace App\DTOs\Proyecto;

/**
 * DTO para actualización de Proyecto
 * 
 * Encapsula los datos necesarios para actualizar un proyecto existente.
 */
class ActualizarProyectoDTO
{
    /**
     * Constructor.
     *
     * @param string|null $descripcion
     * @param float|null $montoActual
     * @param float|null $montoObjetivo
     * @param string|null $fechaAlta
     * @param string|null $fechaRealizacion
     */
    public function __construct(
        public readonly ?string $descripcion = null,
        public readonly ?float $montoActual = null,
        public readonly ?float $montoObjetivo = null,
        public readonly ?string $fechaAlta = null,
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
            descripcion: $datos['descripcion'] ?? null,
            montoActual: isset($datos['monto_actual']) ? (float) $datos['monto_actual'] : null,
            montoObjetivo: isset($datos['monto_objetivo']) ? (float) $datos['monto_objetivo'] : null,
            fechaAlta: $datos['fecha_alta'] ?? null,
            fechaRealizacion: array_key_exists('fecha_realizacion', $datos) ? $datos['fecha_realizacion'] : null,
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

        if ($this->descripcion !== null) {
            $datos['descripcion'] = $this->descripcion;
        }

        if ($this->montoActual !== null) {
            $datos['monto_actual'] = $this->montoActual;
        }

        if ($this->montoObjetivo !== null) {
            $datos['monto_objetivo'] = $this->montoObjetivo;
        }

        if ($this->fechaAlta !== null) {
            $datos['fecha_alta'] = $this->fechaAlta;
        }

        if ($this->fechaRealizacion !== null) {
            $datos['fecha_realizacion'] = $this->fechaRealizacion;
        }

        return $datos;
    }
}
