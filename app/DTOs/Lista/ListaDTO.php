<?php

namespace App\DTOs\Lista;

/**
 * DTO para creación/actualización de Lista
 * 
 * Encapsula los datos necesarios para crear o actualizar una lista.
 */
class ListaDTO
{
    /**
     * Constructor.
     *
     * @param string $nombre
     * @param string|null $descripcion
     * @param string|null $color
     */
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $descripcion = null,
        public readonly ?string $color = null,
    ) {}

    /**
     * Crear DTO desde un array de datos.
     *
     * @param array<string, mixed> $datos
     * @return self
     */
    public static function desdeArray(array $datos): self
    {
        return new self(
            nombre: $datos['nombre'],
            descripcion: $datos['descripcion'] ?? null,
            color: $datos['color'] ?? null,
        );
    }

    /**
     * Convertir DTO a array para almacenamiento.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'color' => $this->color,
        ];
    }
}
