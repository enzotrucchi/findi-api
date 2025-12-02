<?php

namespace App\DTOs\Organizacion;

use App\Models\Organizacion;

/**
 * DTO para Organizacion
 * 
 * Objeto de transferencia de datos inmutable para representar
 * una organización en las respuestas de la API.
 */
class OrganizacionDTO
{
    /**
     * Constructor privado para forzar el uso de métodos factory.
     */
    private function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $fechaAlta,
        public readonly bool $esPrueba,
        public readonly ?string $fechaFinPrueba,
        public readonly string $fechaCreacion,
        public readonly string $fechaActualizacion,
    ) {}

    /**
     * Crear DTO desde un modelo Eloquent.
     *
     * @param Organizacion $organizacion
     * @return self
     */
    public static function desdeModelo(Organizacion $organizacion): self
    {
        return new self(
            id: $organizacion->id,
            nombre: $organizacion->nombre,
            fechaAlta: $organizacion->fecha_alta,
            esPrueba: $organizacion->es_prueba,
            fechaFinPrueba: $organizacion->fecha_fin_prueba,
            fechaCreacion: $organizacion->created_at->toIso8601String(),
            fechaActualizacion: $organizacion->updated_at->toIso8601String(),
        );
    }

    /**
     * Convertir DTO a array.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'fechaAlta' => $this->fechaAlta,
            'esPrueba' => $this->esPrueba,
            'fechaFinPrueba' => $this->fechaFinPrueba,
            'fechaCreacion' => $this->fechaCreacion,
            'fechaActualizacion' => $this->fechaActualizacion,
        ];
    }
}
