<?php

namespace App\DTOs\Asociado;

use App\Models\Asociado;

/**
 * DTO para Asociado
 * 
 * Objeto de transferencia de datos inmutable para representar
 * un asociado en las respuestas de la API.
 */
class AsociadoDTO
{
    /**
     * Constructor privado para forzar el uso de métodos factory.
     */
    private function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly ?string $domicilio,
        public readonly string $fechaCreacion,
        public readonly string $fechaActualizacion,
    ) {}

    /**
     * Crear DTO desde un modelo Eloquent.
     *
     * @param Asociado $asociado
     * @return self
     */
    public static function desdeModelo(Asociado $asociado): self
    {
        return new self(
            id: $asociado->id,
            nombre: $asociado->nombre,
            email: $asociado->email,
            telefono: $asociado->telefono,
            domicilio: $asociado->domicilio,
            fechaCreacion: $asociado->created_at->toIso8601String(),
            fechaActualizacion: $asociado->updated_at->toIso8601String(),
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
            'domicilio' => $this->domicilio,
            'fechaCreacion' => $this->fechaCreacion,
            'fechaActualizacion' => $this->fechaActualizacion,
        ];
    }
}
