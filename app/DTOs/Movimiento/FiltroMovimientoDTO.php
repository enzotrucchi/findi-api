<?php

namespace App\DTOs\Movimiento;

class FiltroMovimientoDTO
{
    private int $pagina = 1;
    private ?string $fecha_desde = null;
    private ?string $fecha_hasta = null;
    private ?string $tipo = null;
    private ?int $lista_id = null;

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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getListaId(): ?int
    {
        return $this->lista_id;
    }

    public function setListaId(?int $listaId): void
    {
        $this->lista_id = $listaId;
    }
}
