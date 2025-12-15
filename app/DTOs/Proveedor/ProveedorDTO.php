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
        public readonly ?int $organizacionId = null,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly bool $activo,
        public readonly string $fechaCreacion,
        public readonly string $fechaActualizacion,
    ) {}

    public static function desdeArray(array $datos): self
    {
        return new self(
            id: $datos['id'] ?? 0,
            organizacionId: $datos['organizacion_id'] ?? null,
            nombre: $datos['nombre'],
            email: $datos['email'],
            telefono: $datos['telefono'],
            activo: $datos['activo'] ?? true,
            fechaCreacion: $datos['fechaCreacion'] ?? now()->toIso8601String(),
            fechaActualizacion: $datos['fechaActualizacion'] ?? now()->toIso8601String(),
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
            'organizacion_id' => $this->organizacionId,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
            'fechaCreacion' => $this->fechaCreacion,
            'fechaActualizacion' => $this->fechaActualizacion,
        ];
    }
}
