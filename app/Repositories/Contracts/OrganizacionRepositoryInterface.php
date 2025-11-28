<?php

namespace App\Repositories\Contracts;

use App\Models\Organizacion;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para el repositorio de Organizaciones
 * 
 * Define el contrato para las operaciones de acceso a datos
 * de organizaciones.
 */
interface OrganizacionRepositoryInterface
{
    /**
     * Obtener todas las organizaciones.
     *
     * @return Collection<int, Organizacion>
     */
    public function obtenerTodos(): Collection;

    /**
     * Obtener organizaciones de prueba.
     *
     * @return Collection<int, Organizacion>
     */
    public function obtenerPrueba(): Collection;

    /**
     * Obtener organizaciones de producción.
     *
     * @return Collection<int, Organizacion>
     */
    public function obtenerProduccion(): Collection;

    /**
     * Obtener una organización por ID.
     *
     * @param int $id
     * @return Organizacion|null
     */
    public function obtenerPorId(int $id): ?Organizacion;

    /**
     * Crear una nueva organización.
     *
     * @param array<string, mixed> $datos
     * @return Organizacion
     */
    public function crear(array $datos): Organizacion;

    /**
     * Actualizar una organización existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool;

    /**
     * Eliminar una organización.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool;

    /**
     * Buscar organizaciones por término.
     *
     * @param string $termino
     * @return Collection<int, Organizacion>
     */
    public function buscar(string $termino): Collection;

    /**
     * Verificar si un nombre ya existe.
     *
     * @param string $nombre
     * @param int|null $excluirId ID a excluir de la búsqueda (para actualizaciones)
     * @return bool
     */
    public function existeNombre(string $nombre, ?int $excluirId = null): bool;
}
