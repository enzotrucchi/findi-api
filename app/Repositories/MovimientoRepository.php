<?php

namespace App\Repositories;

use App\Models\Movimiento;
use App\Repositories\Contracts\MovimientoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio de Movimientos
 * 
 * Maneja todas las operaciones de acceso a datos de movimientos.
 * No contiene lógica de negocio.
 */
class MovimientoRepository implements MovimientoRepositoryInterface
{
    /**
     * Obtener todos los movimientos.
     *
     * @return Collection<int, Movimiento>
     */
    public function obtenerColeccion(): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Obtener movimientos por organización.
     *
     * @param int $organizacionId
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorOrganizacion(int $organizacionId): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago'])
            ->where('organizacion_id', $organizacionId)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Obtener movimientos por proyecto.
     *
     * @param int $proyectoId
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorProyecto(int $proyectoId): Collection
    {
        return Movimiento::with(['asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->where('proyecto_id', $proyectoId)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Obtener movimientos por tipo.
     *
     * @param string $tipo
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorTipo(string $tipo): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->where('tipo', $tipo)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }


    /**
     * Obtener suma por tipo
     */
    public function obtenerSumaPorTipo(string $tipo): float
    {
        return Movimiento::where('tipo', $tipo)->sum('monto');
    }


    /**
     * Obtener movimientos por status.
     *
     * @param string $status
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorStatus(string $status): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->where('status', $status)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Obtener movimientos por rango de fechas.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorRangoFechas(string $fechaInicio, string $fechaFin): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Obtener un movimiento por ID.
     *
     * @param int $id
     * @return Movimiento|null
     */
    public function obtenerPorId(int $id): ?Movimiento
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->find($id);
    }

    /**
     * Crear un nuevo movimiento.
     *
     * @param array<string, mixed> $datos
     * @return Movimiento
     */
    public function crear(array $datos): Movimiento
    {
        return Movimiento::create($datos);
    }

    /**
     * Actualizar un movimiento existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool
    {
        $movimiento = Movimiento::find($id);

        if (!$movimiento) {
            return false;
        }

        return $movimiento->update($datos);
    }

    /**
     * Eliminar un movimiento.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $movimiento = Movimiento::find($id);

        if (!$movimiento) {
            return false;
        }

        return $movimiento->delete();
    }

    /**
     * Buscar movimientos por término.
     *
     * @param string $termino
     * @return Collection<int, Movimiento>
     */
    public function buscar(string $termino): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->where('detalle', 'like', "%{$termino}%")
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Calcular total de ingresos por proyecto.
     *
     * @param int $proyectoId
     * @return float
     */
    public function calcularTotalIngresosPorProyecto(int $proyectoId): float
    {
        return Movimiento::where('proyecto_id', $proyectoId)
            ->where('tipo', 'ingreso')
            ->sum('monto');
    }

    /**
     * Calcular total de egresos por proyecto.
     *
     * @param int $proyectoId
     * @return float
     */
    public function calcularTotalEgresosPorProyecto(int $proyectoId): float
    {
        return Movimiento::where('proyecto_id', $proyectoId)
            ->where('tipo', 'egreso')
            ->sum('monto');
    }

    /**
     * Obtener movimientos por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorIds(array $ids): Collection
    {
        return Movimiento::with(['proyecto', 'asociado', 'proveedor', 'modoPago', 'organizacion'])
            ->whereIn('id', $ids)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();
    }

    /**
     * Verificar si existe un movimiento por ID.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool
    {
        return Movimiento::where('id', $id)->exists();
    }

    /**
     * Contar total de movimientos.
     *
     * @param array<string, mixed> $filtros
     * @return int
     */
    public function contarColeccion(array $filtros = []): int
    {
        $query = Movimiento::query();

        if (isset($filtros['organizacion_id'])) {
            $query->where('organizacion_id', $filtros['organizacion_id']);
        }

        if (isset($filtros['proyecto_id'])) {
            $query->where('proyecto_id', $filtros['proyecto_id']);
        }

        if (isset($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (isset($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $query->whereBetween('fecha', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
        }

        return $query->count();
    }
}
