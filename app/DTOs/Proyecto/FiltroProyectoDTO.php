<?php

namespace App\DTOs\Proyecto;

/**
 * DTO para filtrar Proyectos
 * 
 * Encapsula los criterios de filtrado para consultas de proyectos.
 */
class FiltroProyectoDTO
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
