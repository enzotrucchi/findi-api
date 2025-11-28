<?php

namespace App\Repositories\Contracts;

use App\Models\Asociado;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para el repositorio de Asociados
 * 
 * Define el contrato para las operaciones de acceso a datos
 * de asociados.
 */
interface AsociadoRepositoryInterface
{
    /**
     * Obtener todos los asociados.
     *
     * @return Collection<int, Asociado>
     */
    public function obtenerColeccion(): Collection;

    /**
     * Obtener asociados activos.
     *
     * @return Collection<int, Asociado>
     */
    public function obtenerActivos(): Collection;

    /**
     * Obtener asociados administradores.
     *
     * @return Collection<int, Asociado>
     */
    public function obtenerAdministradores(): Collection;

    /**
     * Obtener un asociado por ID.
     *
     * @param int $id
     * @return Asociado|null
     */
    public function obtenerPorId(int $id): ?Asociado;

    /**
     * Crear un nuevo asociado.
     *
     * @param array<string, mixed> $datos
     * @return Asociado
     */
    public function crear(array $datos): Asociado;

    /**
     * Actualizar un asociado existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool;

    /**
     * Eliminar un asociado.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool;

    /**
     * Buscar asociados por término.
     *
     * @param string $termino
     * @return Collection<int, Asociado>
     */
    public function buscar(string $termino): Collection;

    /**
     * Verificar si un email ya existe.
     *
     * @param string $email
     * @param int|null $excluirId ID a excluir de la búsqueda (para actualizaciones)
     * @return bool
     */
    public function existeEmail(string $email, ?int $excluirId = null): bool;

    /**
     * Obtener asociados por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, Asociado>
     */
    public function obtenerPorIds(array $ids): Collection;

    /**
     * Verificar si existe un asociado por ID.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool;

    /**
     * Contar total de asociados.
     *
     * @param bool $soloActivos
     * @return int
     */
    public function contarColeccion(bool $soloActivos = false): int;
}
