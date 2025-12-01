<?php

namespace App\DTOs\Asociado;

/**
 * DTO para actualización de Asociado
 * 
 * Encapsula los datos necesarios para actualizar un asociado existente.
 */
class ActualizarAsociadoDTO
{
    /**
     * Constructor.
     *
     * @param string|null $nombre
     * @param string|null $email
     * @param string|null $telefono
     * @param string|null $domicilio
     * @param bool|null $esAdmin
     * @param bool|null $activo
     */
    public function __construct(
        public readonly ?string $nombre = null,
        public readonly ?string $email = null,
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
            nombre: $datos['nombre'] ?? null,
            email: $datos['email'] ?? null,
            telefono: $datos['telefono'] ?? null,
            domicilio: array_key_exists('domicilio', $datos) ? $datos['domicilio'] : null,
        );
    }

    /**
     * Convertir DTO a array para almacenamiento.
     * Solo incluye los campos que no son null.
     *
     * @return array<string, mixed>
     */
    public function aArray(): array
    {
        $datos = [];

        if ($this->nombre !== null) {
            $datos['nombre'] = $this->nombre;
        }

        if ($this->email !== null) {
            $datos['email'] = $this->email;
        }

        if ($this->telefono !== null) {
            $datos['telefono'] = $this->telefono;
        }

        if ($this->domicilio !== null) {
            $datos['domicilio'] = $this->domicilio;
        }

        return $datos;
    }
}
