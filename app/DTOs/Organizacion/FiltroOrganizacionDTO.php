<?php

namespace App\DTOs\Organizacion;

class FiltroOrganizacionDTO
{
    private ?bool $solo_prueba = null;
    private ?bool $solo_produccion = null;
    private ?bool $solo_habilitadas = null;
    private ?string $search = null;

    public function __construct() {}

    public function getSoloPrueba(): ?bool
    {
        return $this->solo_prueba;
    }

    public function setSoloPrueba(?bool $soloPrueba): void
    {
        $this->solo_prueba = $soloPrueba;
    }

    public function getSoloProduccion(): ?bool
    {
        return $this->solo_produccion;
    }

    public function setSoloProduccion(?bool $soloProduccion): void
    {
        $this->solo_produccion = $soloProduccion;
    }

    public function getSoloHabilitadas(): ?bool
    {
        return $this->solo_habilitadas;
    }

    public function setSoloHabilitadas(?bool $soloHabilitadas): void
    {
        $this->solo_habilitadas = $soloHabilitadas;
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
