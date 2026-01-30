<?php

namespace App\DTOs\Lista;

class FiltroListaDTO
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
