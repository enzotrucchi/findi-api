<?php

namespace App\DTOs\Proveedor;

/**
 * DTO para filtrar Proveedores
 * 
 * Encapsula los criterios de filtrado para consultas de proveedores.
 */
class FiltroProveedorDTO
{
    /**
     * Constructor.
     *
     * @param bool|null $soloActivos
     * @param string|null $busqueda
     */
    public function __construct(
        public readonly ?bool $soloActivos = null,
        public readonly ?string $busqueda = null,
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
            soloActivos: isset($datos['activos']) ? (bool) $datos['activos'] : null,
            busqueda: $datos['busqueda'] ?? null,
        );
    }
}
