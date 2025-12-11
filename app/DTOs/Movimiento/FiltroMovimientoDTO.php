<?php

namespace App\DTOs\Movimiento;

class FiltroMovimientoDTO
{
    private int $pagina = 1;
    private ?string $fecha_desde = null;
    private ?string $fecha_hasta = null;

    public function __construct() {}

    public function getPagina(): int
    {
        return $this->pagina;
    }

    public function setPagina(int $pagina): void
    {
        $this->pagina = $pagina;
    }

    public function getFechaDesde(): ?string
    {
        return $this->fecha_desde;
    }

    public function setFechaDesde(?string $fechaDesde): void
    {
        $this->fecha_desde = $fechaDesde;
    }

    public function getFechaHasta(): ?string
    {
        return $this->fecha_hasta;
    }

    public function setFechaHasta(?string $fechaHasta): void
    {
        $this->fecha_hasta = $fechaHasta;
    }
}
