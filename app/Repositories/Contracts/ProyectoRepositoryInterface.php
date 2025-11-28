<?php

namespace App\Repositories\Contracts;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para el repositorio de Proyectos
 * 
 * Define el contrato para las operaciones de acceso a datos
 * de proyectos.
 */
interface ProyectoRepositoryInterface
{
    /**
     * Obtener todos los proyectos.
     *
     * @return Collection<int, Proyecto>
     */
    public function obtenerColeccion(): Collection;

    /**
     * Obtener proyectos activos (sin fecha de realización).
     *
     * @return Collection<int, Proyecto>
     */
    public function obtenerActivos(): Collection;

    /**
     * Obtener proyectos finalizados (con fecha de realización).
     *
     * @return Collection<int, Proyecto>
     */
    public function obtenerFinalizados(): Collection;

    /**
     * Obtener un proyecto por ID.
     *
     * @param int $id
     * @return Proyecto|null
     */
    public function obtenerPorId(int $id): ?Proyecto;

    /**
     * Crear un nuevo proyecto.
     *
     * @param array<string, mixed> $datos
     * @return Proyecto
     */
    public function crear(array $datos): Proyecto;

    /**
     * Actualizar un proyecto existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool;

    /**
     * Eliminar un proyecto.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool;

    /**
     * Buscar proyectos por término.
     *
     * @param string $termino
     * @return Collection<int, Proyecto>
     */
    public function buscar(string $termino): Collection;

    /**
     * Obtener proyectos por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, Proyecto>
     */
    public function obtenerPorIds(array $ids): Collection;

    /**
     * Verificar si existe un proyecto por ID.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool;

    /**
     * Contar total de proyectos.
     *
     * @param bool $soloActivos
     * @return int
     */
    public function contarColeccion(bool $soloActivos = false): int;
}
