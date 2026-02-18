<?php

namespace App\Services;

use App\DTOs\Proyecto\ProyectoDTO;
use App\Services\Traits\ObtenerOrganizacionSeleccionada;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Auth;
use App\DTOs\Proyecto\FiltroProyectoDTO;

/**
 * Servicio de Proyectos
 * 
 * Contiene toda la lógica de negocio relacionada con proyectos.
 */
class ProyectoService
{
    use ObtenerOrganizacionSeleccionada;

    public function __construct() {}

    /**
     * Obtener todos los proyectos.
     *
     */
    public function obtenerColeccion(FiltroProyectoDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Proyecto::query();

        $total = (clone $query)->count();
        $perPage = $total > 0 ? $total : 1;

        return $query
            ->orderBy('fecha_alta', 'asc')
            ->paginate(perPage: $perPage, columns: ['*'], pageName: 'pagina', page: $filtroDTO->getPagina());
    }


    /**
     * Obtener estadísticas
     * Proyectos totales
     * Proyectos activos
     */
    public function obtenerEstadisticas(): array
    {
        $query = Proyecto::query();

        $totalProyectos = (clone $query)->count();
        $proyectosActivos = (clone $query)->whereNull('fecha_realizacion')->count();

        return [
            'total_proyectos' => $totalProyectos,
            'proyectos_activos' => $proyectosActivos,
        ];
    }

    public function obtenerMovimientosPorProyecto(int $id, ?string $tipo = null): Collection
    {
        $proyecto = Proyecto::find($id);

        if (!$proyecto) {
            throw new InvalidArgumentException('Proyecto no encontrado.');
        }

        $query = $proyecto->movimientos();

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->get();
    }


    /**
     * Crear un nuevo proyecto.
     *
     * @param ProyectoDTO $dto
     * @return Proyecto
     * @throws InvalidArgumentException
     */
    public function crear(ProyectoDTO $dto): Proyecto
    {
        $orgId = $this->obtenerOrganizacionId();

        $proyecto = Proyecto::create([
            'descripcion' => $dto->descripcion,
            'monto_actual' => $dto->montoActual,
            'monto_objetivo' => $dto->montoObjetivo,
            'fecha_alta' => $dto->fechaAlta,
            'fecha_realizacion' => $dto->fechaRealizacion,
            'organizacion_id' => $orgId,
        ]);

        return $proyecto;
    }

    /**
     * Actualizar un proyecto existente.
     *
     * @param int $id
     * @param ProyectoDTO $dto
     * @return Proyecto|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ProyectoDTO $dto): ?Proyecto
    {
        $query = Proyecto::query();

        $proyecto = $query->find($id);

        if (!$proyecto) {
            return null;
        }

        // Validaciones de negocio
        $montoObjetivo = $dto->montoObjetivo ?? $proyecto->monto_objetivo;
        $montoActual = $dto->montoActual ?? $proyecto->monto_actual;

        if ($dto->montoObjetivo !== null && $dto->montoObjetivo <= 0) {
            throw new InvalidArgumentException('El monto objetivo debe ser mayor a cero.');
        }

        if ($dto->montoActual !== null && $dto->montoActual < 0) {
            throw new InvalidArgumentException('El monto actual no puede ser negativo.');
        }

        if ($montoActual > $montoObjetivo) {
            throw new InvalidArgumentException('El monto actual no puede ser mayor al monto objetivo.');
        }

        $datosActualizar = [];

        if ($dto->descripcion !== null) {
            $datosActualizar['descripcion'] = $dto->descripcion;
        }

        if ($dto->montoActual !== null) {
            $datosActualizar['monto_actual'] = $dto->montoActual;
        }

        if ($dto->montoObjetivo !== null) {
            $datosActualizar['monto_objetivo'] = $dto->montoObjetivo;
        }

        if ($dto->fechaAlta !== null) {
            $datosActualizar['fecha_alta'] = $dto->fechaAlta;
        }

        if ($dto->fechaRealizacion !== null) {
            $datosActualizar['fecha_realizacion'] = $dto->fechaRealizacion;
        }

        DB::transaction(function () use ($proyecto, $datosActualizar) {
            $proyecto->update($datosActualizar);
        });

        return $proyecto->fresh();
    }

    /**
     * Eliminar un proyecto.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $query = Proyecto::query();

            $proyecto = $query->find($id);

            if (!$proyecto) {
                return false;
            }

            return $proyecto->delete();
        });
    }
}
