<?php

namespace App\Repositories\Contracts;

use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para el repositorio de Movimientos
 * 
 * Define el contrato para las operaciones de acceso a datos
 * de movimientos.
 */
interface MovimientoRepositoryInterface
{
    /**
     * Obtener todos los movimientos.
     *
     * @return Collection<int, Movimiento>
     */
    public function obtenerColeccion(): Collection;

    /**
     * Obtener movimientos por organización.
     *
     * @param int $organizacionId
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorOrganizacion(int $organizacionId): Collection;

    /**
     * Obtener movimientos por proyecto.
     *
     * @param int $proyectoId
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorProyecto(int $proyectoId): Collection;

    /**
     * Obtener movimientos por tipo.
     *
     * @param string $tipo
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorTipo(string $tipo): Collection;

    /**
     * Obtener movimientos por status.
     *
     * @param string $status
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorStatus(string $status): Collection;

    /**
     * Obtener movimientos por rango de fechas.
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorRangoFechas(string $fechaInicio, string $fechaFin): Collection;

    /**
     * Obtener un movimiento por ID.
     *
     * @param int $id
     * @return Movimiento|null
     */
    public function obtenerPorId(int $id): ?Movimiento;

    /**
     * Crear un nuevo movimiento.
     *
     * @param array<string, mixed> $datos
     * @return Movimiento
     */
    public function crear(array $datos): Movimiento;

    /**
     * Actualizar un movimiento existente.
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool;

    /**
     * Eliminar un movimiento.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool;

    /**
     * Buscar movimientos por término.
     *
     * @param string $termino
     * @return Collection<int, Movimiento>
     */
    public function buscar(string $termino): Collection;

    /**
     * Calcular total de ingresos por proyecto.
     *
     * @param int $proyectoId
     * @return float
     */
    public function calcularTotalIngresosPorProyecto(int $proyectoId): float;

    /**
     * Calcular total de egresos por proyecto.
     *
     * @param int $proyectoId
     * @return float
     */
    public function calcularTotalEgresosPorProyecto(int $proyectoId): float;

    /**
     * Obtener movimientos por múltiples IDs.
     *
     * @param array<int> $ids
     * @return Collection<int, Movimiento>
     */
    public function obtenerPorIds(array $ids): Collection;

    /**
     * Verificar si existe un movimiento por ID.
     *
     * @param int $id
     * @return bool
     */
    public function existePorId(int $id): bool;

    /**
     * Contar total de movimientos.
     *
     * @param array<string, mixed> $filtros
     * @return int
     */
    public function contarColeccion(array $filtros = []): int;
}
