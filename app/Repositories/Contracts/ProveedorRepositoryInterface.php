<?php

namespace App\Repositories\Contracts;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para el repositorio de Proveedores
 * 
 * Define el contrato para las operaciones de acceso a datos
 * de proveedores.
 */
interface ProveedorRepositoryInterface
{
    /**
     * Obtener todos los proveedores.
     *
     * @return Collection<int, Proveedor>
     */
    public function obtenerTodos(): Collection;

    /**
     * Obtener proveedores activos.
     *
     * @return Collection<int, Proveedor>
     */
    public function obtenerActivos(): Collection;

    /**
     * Obtener un proveedor por ID.
     *
     * @param int $id
     * @return Proveedor|null
     */
    public function obtenerPorId(int $id): ?Proveedor;

    /**
     * Crear un nuevo proveedor.
     *
     * @param array<string, mixed> $datos
     * @return Proveedor
     */
    public function crear(array $datos): Proveedor;

    /**
     * Actualizar un proveedor existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool;

    /**
     * Eliminar un proveedor.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool;

    /**
     * Buscar proveedores por término.
     *
     * @param string $termino
     * @return Collection<int, Proveedor>
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
}
