<?php

namespace App\DTOs\Asociado;

/**
 * DTO para creación de Asociado
 * 
 * Encapsula los datos necesarios para crear un nuevo asociado.
 */
class CrearAsociadoDTO
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
        public readonly string $telefono,
        public readonly ?string $domicilio = null,
        public readonly bool $esAdmin = false,
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
            domicilio: $datos['domicilio'] ?? null,
            esAdmin: $datos['es_admin'] ?? false,
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
            'domicilio' => $this->domicilio,
            'es_admin' => $this->esAdmin,
            'activo' => $this->activo,
        ];
    }
}
