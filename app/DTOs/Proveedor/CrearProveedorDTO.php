<?php

namespace App\DTOs\Proveedor;

/**
 * DTO para creación de Proveedor
 * 
 * Encapsula los datos necesarios para crear un nuevo proveedor.
 */
class CrearProveedorDTO
{
    /**
     * Constructor.
     *
     * @param string $nombre
     * @param string $email
     * @param string $telefono
     * @param bool $activo
     */
    public function __construct(
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly bool $activo = true,
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
            email: $datos['email'],
            telefono: $datos['telefono'],
            activo: $datos['activo'] ?? true,
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
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
        ];
    }
}
