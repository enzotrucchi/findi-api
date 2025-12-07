<?php

namespace App\DTOs\Asociado;

/**
 * DTO para creación de Asociado
 * 
 * Encapsula los datos necesarios para crear un nuevo asociado.
 */
class AsociadoDTO
{
    /**
     * Constructor.
     *
     * @param string $nombre
     * @param string $email
     * @param string $telefono
     * @param string|null $domicilio
     * @param bool $esAdmin
     * @param bool $activo
     */
    public function __construct(
        public readonly string $nombre,
        public readonly string $email,
        public readonly ?string $telefono = null,
        public readonly ?string $domicilio = null,
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
            telefono: $datos['telefono'] ?? null,
            domicilio: $datos['domicilio'] ?? null,
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
            'domicilio' => $this->domicilio,
        ];
    }
}
