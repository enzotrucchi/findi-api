<?php

namespace App\Repositories;

use App\Models\Organizacion;
use App\Repositories\Contracts\OrganizacionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio de Organizaciones
 * 
 * Maneja todas las operaciones de acceso a datos de organizaciones.
 * No contiene lógica de negocio.
 */
class OrganizacionRepository implements OrganizacionRepositoryInterface
{
    /**
     * Obtener todas las organizaciones.
     *
     * @return Collection<int, Organizacion>
     */
    public function obtenerTodos(): Collection
    {
        return Organizacion::orderBy('nombre')->get();
    }

    /**
     * Obtener organizaciones de prueba.
     *
     * @return Collection<int, Organizacion>
     */
    public function obtenerPrueba(): Collection
    {
        return Organizacion::where('es_prueba', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener organizaciones de producción.
     *
     * @return Collection<int, Organizacion>
     */
    public function obtenerProduccion(): Collection
    {
        return Organizacion::where('es_prueba', false)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener una organización por ID.
     *
     * @param int $id
     * @return Organizacion|null
     */
    public function obtenerPorId(int $id): ?Organizacion
    {
        return Organizacion::find($id);
    }

    /**
     * Crear una nueva organización.
     *
     * @param array<string, mixed> $datos
     * @return Organizacion
     */
    public function crear(array $datos): Organizacion
    {
        return Organizacion::create($datos);
    }

    /**
     * Actualizar una organización existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        $organizacion = $this->obtenerPorId($id);
        
        if (!$organizacion) {
            return false;
        }

        return $organizacion->update($datos);
    }

    /**
     * Eliminar una organización.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $organizacion = $this->obtenerPorId($id);
        
        if (!$organizacion) {
            return false;
        }

        return $organizacion->delete();
    }

    /**
     * Buscar organizaciones por término.
     *
     * @param string $termino
     * @return Collection<int, Organizacion>
     */
    public function buscar(string $termino): Collection
    {
        return Organizacion::where('nombre', 'like', "%{$termino}%")
            ->orWhere('admin_email', 'like', "%{$termino}%")
            ->orWhere('admin_nombre', 'like', "%{$termino}%")
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Verificar si un nombre ya existe.
     *
     * @param string $nombre
     * @param int|null $excluirId ID a excluir de la búsqueda (para actualizaciones)
     * @return bool
     */
    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $query = Organizacion::where('nombre', $nombre);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }
}
