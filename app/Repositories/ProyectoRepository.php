<?php

namespace App\Repositories;

use App\Models\Proyecto;
use App\Repositories\Contracts\ProyectoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio de Proyectos
 * 
 * Maneja todas las operaciones de acceso a datos de proyectos.
 * No contiene lógica de negocio.
 */
class ProyectoRepository implements ProyectoRepositoryInterface
{
    /**
     * Obtener todos los proyectos.
     *
     * @return Collection<int, Proyecto>
     */
    public function obtenerColeccion(): Collection
    {
        return Proyecto::orderBy('fecha_alta', 'desc')->get();
    }

    /**
     * Obtener proyectos activos (sin fecha de realización).
     *
     * @return Collection<int, Proyecto>
     */
    public function obtenerActivos(): Collection
    {
        return Proyecto::whereNull('fecha_realizacion')
            ->orderBy('fecha_alta', 'desc')
            ->get();
    }

    /**
     * Obtener proyectos finalizados (con fecha de realización).
     *
     * @return Collection<int, Proyecto>
     */
    public function obtenerFinalizados(): Collection
    {
        return Proyecto::whereNotNull('fecha_realizacion')
            ->orderBy('fecha_realizacion', 'desc')
            ->get();
    }

    /**
     * Obtener un proyecto por ID.
     *
     * @param int $id
     * @return Proyecto|null
     */
    public function obtenerPorId(int $id): ?Proyecto
    {
        return Proyecto::find($id);
    }

    /**
     * Crear un nuevo proyecto.
     *
     * @param array<string, mixed> $datos
     * @return Proyecto
     */
    public function crear(array $datos): Proyecto
    {
        return Proyecto::create($datos);
    }

    /**
     * Actualizar un proyecto existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        $proyecto = $this->obtenerPorId($id);

        if (!$proyecto) {
            return false;
        }

        return $proyecto->update($datos);
    }

    /**
     * Eliminar un proyecto.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $proyecto = $this->obtenerPorId($id);

        if (!$proyecto) {
            return false;
        }

        return $proyecto->delete();
    }

    /**
     * Buscar proyectos por término.
     *
     * @param string $termino
     * @return Collection<int, Proyecto>
     */
    public function buscar(string $termino): Collection
    {
        return Proyecto::where('descripcion', 'like', "%{$termino}%")
            ->orderBy('fecha_alta', 'desc')
            ->get();
    }

    /**
     * Obtener proyectos por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, Proyecto>
     */
    public function obtenerPorIds(array $ids): Collection
    {
        return Proyecto::whereIn('id', $ids)
            ->orderBy('fecha_alta', 'desc')
            ->get();
    }

    /**
     * Verificar si existe un proyecto por ID.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool
    {
        return Proyecto::where('id', $id)->exists();
    }

    /**
     * Contar total de proyectos.
     *
     * @param bool $soloActivos
     * @return int
     */
    public function contarColeccion(bool $soloActivos = false): int
    {
        $query = Proyecto::query();

        if ($soloActivos) {
            $query->whereNull('fecha_realizacion');
        }

        return $query->count();
    }
}
