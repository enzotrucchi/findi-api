<?php

namespace App\DTOs\Organizacion;

/**
 * DTO para Organizacion
 * 
 * Encapsula los datos necesarios para crear/actualizar una organización.
 */
class OrganizacionDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $fechaAlta = null,
        public readonly ?bool $esPrueba = null,
        public readonly ?string $fechaFinPrueba = null,
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
            nombre: $datos['nombre'],
            fechaAlta: $datos['fecha_alta'] ?? null,
            esPrueba: isset($datos['es_prueba']) ? (bool) $datos['es_prueba'] : null,
            fechaFinPrueba: $datos['fecha_fin_prueba'] ?? null,
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
            'nombre' => $this->nombre,
            'fecha_alta' => $this->fechaAlta,
            'es_prueba' => $this->esPrueba,
            'fecha_fin_prueba' => $this->fechaFinPrueba,
        ];
    }
}
