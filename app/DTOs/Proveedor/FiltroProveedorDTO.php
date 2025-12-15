<?php

namespace App\DTOs\Proveedor;

/**
 * DTO para filtrar Proveedores
 * 
 * Encapsula los criterios de filtrado para consultas de proveedores.
 */
class FiltroProveedorDTO
{
    private int $pagina = 1;

    public function __construct() {}

    public function getPagina(): int
    {
        return $this->pagina;
    }

    public function setPagina(int $pagina): void
    {
        $this->pagina = $pagina;
    }
}
