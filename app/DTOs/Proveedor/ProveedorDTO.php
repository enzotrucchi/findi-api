<?php

namespace App\DTOs\Proveedor;

use App\Models\Proveedor;

/**
 * DTO para Proveedor
 * 
 * Objeto de transferencia de datos inmutable para representar
 * un proveedor en las respuestas de la API.
 */
class ProveedorDTO
{
    /**
     * Constructor privado para forzar el uso de métodos factory.
     */
    private function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly bool $activo,
        public readonly string $fechaCreacion,
        public readonly string $fechaActualizacion,
    ) {}

    /**
     * Crear DTO desde un modelo Eloquent.
     *
     * @param Proveedor $proveedor
     * @return self
     */
    public static function desdeModelo(Proveedor $proveedor): self
    {
        return new self(
            id: $proveedor->id,
            nombre: $proveedor->nombre,
            email: $proveedor->email,
            telefono: $proveedor->telefono,
            activo: $proveedor->activo,
            fechaCreacion: $proveedor->created_at->toIso8601String(),
            fechaActualizacion: $proveedor->updated_at->toIso8601String(),
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
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
            'fechaCreacion' => $this->fechaCreacion,
            'fechaActualizacion' => $this->fechaActualizacion,
        ];
    }
}
