<?php

namespace App\DTOs\Organizacion;

/**
 * DTO para actualización de Organizacion
 * 
 * Encapsula los datos necesarios para actualizar una organización existente.
 */
class ActualizarOrganizacionDTO
{
    /**
     * Constructor.
     *
     * @param string|null $nombre
     * @param string|null $adminEmail
     * @param string|null $adminNombre
     * @param string|null $fechaAlta
     * @param bool|null $esPrueba
     * @param string|null $fechaFinPrueba
     */
    public function __construct(
        public readonly ?string $nombre = null,
        public readonly ?string $adminEmail = null,
        public readonly ?string $adminNombre = null,
        public readonly ?string $fechaAlta = null,
        public readonly ?bool $esPrueba = null,
        public readonly ?string $fechaFinPrueba = null,
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
            adminEmail: $datos['admin_email'] ?? null,
            adminNombre: $datos['admin_nombre'] ?? null,
            fechaAlta: $datos['fecha_alta'] ?? null,
            esPrueba: isset($datos['es_prueba']) ? (bool) $datos['es_prueba'] : null,
            fechaFinPrueba: array_key_exists('fecha_fin_prueba', $datos) ? $datos['fecha_fin_prueba'] : null,
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

        if ($this->adminEmail !== null) {
            $datos['admin_email'] = $this->adminEmail;
        }

        if ($this->adminNombre !== null) {
            $datos['admin_nombre'] = $this->adminNombre;
        }

        if ($this->fechaAlta !== null) {
            $datos['fecha_alta'] = $this->fechaAlta;
        }

        if ($this->esPrueba !== null) {
            $datos['es_prueba'] = $this->esPrueba;
        }

        if ($this->fechaFinPrueba !== null) {
            $datos['fecha_fin_prueba'] = $this->fechaFinPrueba;
        }

        return $datos;
    }
}
