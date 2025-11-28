<?php

namespace App\Services;

use App\DTOs\Proyecto\ActualizarProyectoDTO;
use App\DTOs\Proyecto\CrearProyectoDTO;
use App\DTOs\Proyecto\ProyectoDTO;
use App\Repositories\Contracts\ProyectoRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Proyectos
 * 
 * Contiene toda la lógica de negocio relacionada con proyectos.
 */
class ProyectoService
{
    /**
     * Constructor.
     *
     * @param ProyectoRepositoryInterface $proyectoRepository
     */
    public function __construct(
        private ProyectoRepositoryInterface $proyectoRepository
    ) {}

    /**
     * Obtener todos los proyectos.
     *
     * @param bool $soloActivos
     * @param bool $soloFinalizados
     * @return Collection<int, ProyectoDTO>
     */
    public function obtenerTodos(bool $soloActivos = false, bool $soloFinalizados = false): Collection
    {
        if ($soloActivos) {
            $proyectos = $this->proyectoRepository->obtenerActivos();
        } elseif ($soloFinalizados) {
            $proyectos = $this->proyectoRepository->obtenerFinalizados();
        } else {
            $proyectos = $this->proyectoRepository->obtenerTodos();
        }

        return $proyectos->map(fn($proyecto) => ProyectoDTO::desdeModelo($proyecto));
    }

    /**
     * Obtener un proyecto por ID.
     *
     * @param int $id
     * @return ProyectoDTO|null
     */
    public function obtenerPorId(int $id): ?ProyectoDTO
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);

        if (!$proyecto) {
            return null;
        }

        return ProyectoDTO::desdeModelo($proyecto);
    }

    /**
     * Crear un nuevo proyecto.
     *
     * @param CrearProyectoDTO $dto
     * @return ProyectoDTO
     * @throws InvalidArgumentException
     */
    public function crear(CrearProyectoDTO $dto): ProyectoDTO
    {
        // Validaciones de negocio
        if ($dto->montoObjetivo <= 0) {
            throw new InvalidArgumentException('El monto objetivo debe ser mayor a cero.');
        }

        if ($dto->montoActual < 0) {
            throw new InvalidArgumentException('El monto actual no puede ser negativo.');
        }

        if ($dto->montoActual > $dto->montoObjetivo) {
            throw new InvalidArgumentException('El monto actual no puede ser mayor al monto objetivo.');
        }

        $proyecto = DB::transaction(function () use ($dto) {
            return $this->proyectoRepository->crear($dto->aArray());
        });

        return ProyectoDTO::desdeModelo($proyecto);
    }

    /**
     * Actualizar un proyecto existente.
     *
     * @param int $id
     * @param ActualizarProyectoDTO $dto
     * @return ProyectoDTO|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, ActualizarProyectoDTO $dto): ?ProyectoDTO
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);

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

        DB::transaction(function () use ($id, $dto) {
            $this->proyectoRepository->actualizar($id, $dto->aArray());
        });

        // Refrescar el modelo
        $proyectoActualizado = $this->proyectoRepository->obtenerPorId($id);

        return ProyectoDTO::desdeModelo($proyectoActualizado);
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
            return $this->proyectoRepository->eliminar($id);
        });
    }

    /**
     * Buscar proyectos por término.
     *
     * @param string $termino
     * @return Collection<int, ProyectoDTO>
     */
    public function buscar(string $termino): Collection
    {
        $proyectos = $this->proyectoRepository->buscar($termino);
        return $proyectos->map(fn($proyecto) => ProyectoDTO::desdeModelo($proyecto));
    }
}
