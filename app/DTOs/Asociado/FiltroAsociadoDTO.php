<?php

namespace App\DTOs\Asociado;

class FiltroAsociadoDTO
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
