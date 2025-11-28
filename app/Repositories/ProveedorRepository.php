<?php

namespace App\Repositories;

use App\Models\Proveedor;
use App\Repositories\Contracts\ProveedorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio de Proveedores
 * 
 * Maneja todas las operaciones de acceso a datos de proveedores.
 * No contiene lógica de negocio.
 */
class ProveedorRepository implements ProveedorRepositoryInterface
{
    /**
     * Obtener todos los proveedores.
     *
     * @return Collection<int, Proveedor>
     */
    public function obtenerTodos(): Collection
    {
        return Proveedor::orderBy('nombre')->get();
    }

    /**
     * Obtener proveedores activos.
     *
     * @return Collection<int, Proveedor>
     */
    public function obtenerActivos(): Collection
    {
        return Proveedor::where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener un proveedor por ID.
     *
     * @param int $id
     * @return Proveedor|null
     */
    public function obtenerPorId(int $id): ?Proveedor
    {
        return Proveedor::find($id);
    }

    /**
     * Crear un nuevo proveedor.
     *
     * @param array<string, mixed> $datos
     * @return Proveedor
     */
    public function crear(array $datos): Proveedor
    {
        return Proveedor::create($datos);
    }

    /**
     * Actualizar un proveedor existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        $proveedor = $this->obtenerPorId($id);
        
        if (!$proveedor) {
            return false;
        }

        return $proveedor->update($datos);
    }

    /**
     * Eliminar un proveedor.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $proveedor = $this->obtenerPorId($id);
        
        if (!$proveedor) {
            return false;
        }

        return $proveedor->delete();
    }

    /**
     * Buscar proveedores por término.
     *
     * @param string $termino
     * @return Collection<int, Proveedor>
     */
    public function buscar(string $termino): Collection
    {
        return Proveedor::where('nombre', 'like', "%{$termino}%")
            ->orWhere('email', 'like', "%{$termino}%")
            ->orWhere('telefono', 'like', "%{$termino}%")
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Verificar si un email ya existe.
     *
     * @param string $email
     * @param int|null $excluirId ID a excluir de la búsqueda (para actualizaciones)
     * @return bool
     */
    public function existeEmail(string $email, ?int $excluirId = null): bool
    {
        $query = Proveedor::where('email', $email);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
