<?php

namespace App\DTOs\PlanPago;

class FiltroPlanPagoDTO
{
    public function __construct(
        private int $pagina = 1,
        private ?int $asociadoId = null,
        private ?string $estado = null,
        private ?string $descripcion = null,
        private ?string $fechaDesde = null,
        private ?string $fechaHasta = null,
    ) {}

    /**
     * @param array<string, mixed> $datos
     */
    public static function desdeArray(array $datos): self
    {
        return new self(
            pagina: isset($datos['pagina']) ? (int) $datos['pagina'] : 1,
            asociadoId: isset($datos['asociado_id']) ? (int) $datos['asociado_id'] : null,
            estado: isset($datos['estado']) ? (string) $datos['estado'] : null,
            descripcion: isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : null,
            fechaDesde: isset($datos['fecha_desde']) ? (string) $datos['fecha_desde'] : null,
            fechaHasta: isset($datos['fecha_hasta']) ? (string) $datos['fecha_hasta'] : null,
        );
    }

    public function getPagina(): int
    {
        return $this->pagina;
    }

    public function getAsociadoId(): ?int
    {
        return $this->asociadoId;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function getFechaDesde(): ?string
    {
        return $this->fechaDesde;
    }

    public function getFechaHasta(): ?string
    {
        return $this->fechaHasta;
    }
}
