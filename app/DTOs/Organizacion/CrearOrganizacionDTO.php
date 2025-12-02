<?php

namespace App\DTOs\Organizacion;

/**
 * DTO para creación de Organizacion
 * 
 * Encapsula los datos necesarios para crear una nueva organización.
 */
class CrearOrganizacionDTO
{
    /**
     * Constructor.
     *
     * @param string $nombre
     * @param string $fechaAlta
     * @param bool $esPrueba
     * @param string|null $fechaFinPrueba
     */
    public function __construct(
        public readonly string $nombre,
        public readonly string $fechaAlta,
        public readonly bool $esPrueba = false,
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
            fechaAlta: $datos['fecha_alta'],
            esPrueba: $datos['es_prueba'] ?? false,
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
