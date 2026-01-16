<?php

namespace App\DTOs\Asociado;

class FiltroAsociadoDTO
{
    private int $pagina = 1;
    private ?string $search = null;

    public function __construct() {}

    public function getPagina(): int
    {
        return $this->pagina;
    }

    public function setPagina(int $pagina): void
    {
        $this->pagina = $pagina;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }
    public function setSearch(?string $search): void
    {
        $s = trim((string) $search);
        $this->search = $s !== '' ? $s : null;
    }
}
