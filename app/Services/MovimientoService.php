<?php

namespace App\Services;

use App\DTOs\Movimiento\ActualizarMovimientoDTO;
use App\DTOs\Movimiento\CrearMovimientoDTO;
use App\DTOs\Movimiento\MovimientoDTO;
use App\Repositories\Contracts\MovimientoRepositoryInterface;
use App\Repositories\Contracts\ProyectoRepositoryInterface;
use App\Repositories\Contracts\AsociadoRepositoryInterface;
use App\Repositories\Contracts\ProveedorRepositoryInterface;
use App\Repositories\Contracts\OrganizacionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Movimientos
 * 
 * Contiene toda la lógica de negocio relacionada con movimientos.
 */
class MovimientoService
{
    /**
     * Constructor.
     *
     */
    public function __construct() {}

    /**
     * Obtener todos los movimientos.
     *
     * @param array<string, mixed> $filtros
     * @return Collection<int, MovimientoDTO>
     */
    // public function obtenerColeccion(array $filtros = []): Collection
    // {


    //     return $movimientos->map(fn($movimiento) => MovimientoDTO::desdeModelo($movimiento));
    // }

    // /**
    //  * Obtener un movimiento por ID.
    //  *
    //  * @param int $id
    //  * @return MovimientoDTO|null
    //  */
    // public function obtenerPorId(int $id): ?MovimientoDTO
    // {
    //     $movimiento = $this->movimientoRepository->obtenerPorId($id);

    //     if (!$movimiento) {
    //         return null;
    //     }

    //     return MovimientoDTO::desdeModelo($movimiento);
    // }

    /**
 * Obtener el balance total de movimientos.
 *
 * @return array<string, float>
 */
    // public function obtenerBalance(): array
    // {
    //     $ingresos = $this->movimientoRepository->obtenerSumaPorTipo('ingreso');
    //     $egresos = $this->movimientoRepository->obtenerSumaPorTipo('egreso');

    //     return [
    //         'ingresos' => $ingresos,
    //         'egresos' => $egresos,
    //         'balance' => $ingresos - $egresos,
    //     ];
    // }

    // /**
    //  * Crear un nuevo movimiento.
    //  *
    //  * @param CrearMovimientoDTO $dto
    //  * @return MovimientoDTO
    //  * @throws InvalidArgumentException
    //  */
    // public function crear(CrearMovimientoDTO $dto): MovimientoDTO
    // {
    //     // Validaciones de negocio
    //     $this->validarDatos($dto);

    //     $movimiento = DB::transaction(function () use ($dto) {
    //         $movimiento = $this->movimientoRepository->crear($dto->aArray());

    //         // Si es un ingreso a un proyecto, actualizar el monto actual del proyecto
    //         if ($dto->proyectoId && $dto->tipo === 'ingreso') {
    //             $this->actualizarMontoProyecto($dto->proyectoId);
    //         }

    //         return $movimiento;
    //     });

    //     return MovimientoDTO::desdeModelo($movimiento);
    // }

    // /**
    //  * Actualizar un movimiento existente.
    //  *
    //  * @param int $id
    //  * @param ActualizarMovimientoDTO $dto
    //  * @return MovimientoDTO|null
    //  * @throws InvalidArgumentException
    //  */
    // public function actualizar(int $id, ActualizarMovimientoDTO $dto): ?MovimientoDTO
    // {
    //     $movimiento = $this->movimientoRepository->obtenerPorId($id);

    //     if (!$movimiento) {
    //         return null;
    //     }

    //     // Validaciones si se están cambiando valores críticos
    //     if ($dto->proyectoId !== null && $dto->proyectoId !== $movimiento->proyecto_id) {
    //         if (!$this->proyectoRepository->obtenerPorId($dto->proyectoId)) {
    //             throw new InvalidArgumentException('El proyecto especificado no existe.');
    //         }
    //     }

    //     if ($dto->asociadoId !== null && $dto->asociadoId !== $movimiento->asociado_id) {
    //         if (!$this->asociadoRepository->obtenerPorId($dto->asociadoId)) {
    //             throw new InvalidArgumentException('El asociado especificado no existe.');
    //         }
    //     }

    //     if ($dto->proveedorId !== null && $dto->proveedorId !== $movimiento->proveedor_id) {
    //         if (!$this->proveedorRepository->obtenerPorId($dto->proveedorId)) {
    //             throw new InvalidArgumentException('El proveedor especificado no existe.');
    //         }
    //     }

    //     if ($dto->organizacionId !== null && $dto->organizacionId !== $movimiento->organizacion_id) {
    //         if (!$this->organizacionRepository->obtenerPorId($dto->organizacionId)) {
    //             throw new InvalidArgumentException('La organización especificada no existe.');
    //         }
    //     }

    //     if ($dto->monto !== null && $dto->monto <= 0) {
    //         throw new InvalidArgumentException('El monto debe ser mayor a cero.');
    //     }

    //     if ($dto->tipo !== null && !in_array($dto->tipo, ['ingreso', 'egreso'])) {
    //         throw new InvalidArgumentException('El tipo debe ser "ingreso" o "egreso".');
    //     }

    //     $proyectoAnterior = $movimiento->proyecto_id;
    //     $tipoAnterior = $movimiento->tipo;

    //     DB::transaction(function () use ($id, $dto, $movimiento, $proyectoAnterior, $tipoAnterior) {
    //         $this->movimientoRepository->actualizar($id, $dto->aArray());

    //         // Recalcular montos si cambió el proyecto o el tipo
    //         if (($dto->proyectoId !== null && $dto->proyectoId !== $proyectoAnterior) ||
    //             ($dto->tipo !== null && $dto->tipo !== $tipoAnterior)
    //         ) {

    //             if ($proyectoAnterior) {
    //                 $this->actualizarMontoProyecto($proyectoAnterior);
    //             }

    //             $nuevoProyecto = $dto->proyectoId ?? $movimiento->proyecto_id;
    //             if ($nuevoProyecto) {
    //                 $this->actualizarMontoProyecto($nuevoProyecto);
    //             }
    //         }
    //     });

    //     // Refrescar el modelo
    //     $movimientoActualizado = $this->movimientoRepository->obtenerPorId($id);

    //     return MovimientoDTO::desdeModelo($movimientoActualizado);
    // }

    // /**
    //  * Eliminar un movimiento.
    //  *
    //  * @param int $id
    //  * @return bool
    //  */
    // public function eliminar(int $id): bool
    // {
    //     $movimiento = $this->movimientoRepository->obtenerPorId($id);

    //     if (!$movimiento) {
    //         return false;
    //     }

    //     $proyectoId = $movimiento->proyecto_id;

    //     $eliminado = DB::transaction(function () use ($id, $proyectoId) {
    //         $eliminado = $this->movimientoRepository->eliminar($id);

    //         // Actualizar monto del proyecto si existía
    //         if ($eliminado && $proyectoId) {
    //             $this->actualizarMontoProyecto($proyectoId);
    //         }

    //         return $eliminado;
    //     });

    //     return $eliminado;
    // }

    // /**
    //  * Buscar movimientos por término.
    //  *
    //  * @param string $termino
    //  * @return Collection<int, MovimientoDTO>
    //  */
    // public function buscar(string $termino): Collection
    // {
    //     $movimientos = $this->movimientoRepository->buscar($termino);
    //     return $movimientos->map(fn($movimiento) => MovimientoDTO::desdeModelo($movimiento));
    // }

    // /**
    //  * Obtener movimientos por múltiples IDs.
    //  *
    //  * @param array<int> $ids
    //  * @return Collection<int, MovimientoDTO>
    //  */
    // public function obtenerPorIds(array $ids): Collection
    // {
    //     $movimientos = $this->movimientoRepository->obtenerPorIds($ids);
    //     return $movimientos->map(fn($movimiento) => MovimientoDTO::desdeModelo($movimiento));
    // }

    // /**
    //  * Verificar si existe un movimiento.
    //  *
    //  * @param int $id
    //  * @return bool
    //  */
    // public function existePorId(int $id): bool
    // {
    //     return $this->movimientoRepository->existePorId($id);
    // }

    // /**
    //  * Contar movimientos.
    //  *
    //  * @param array<string, mixed> $filtros
    //  * @return int
    //  */
    // public function contar(array $filtros = []): int
    // {
    //     return $this->movimientoRepository->contarColeccion($filtros);
    // }

    // /**
    //  * Validar datos del movimiento.
    //  *
    //  * @param CrearMovimientoDTO $dto
    //  * @throws InvalidArgumentException
    //  */
    // private function validarDatos(CrearMovimientoDTO $dto): void
    // {
    //     if ($dto->monto <= 0) {
    //         throw new InvalidArgumentException('El monto debe ser mayor a cero.');
    //     }

    //     if (!in_array($dto->tipo, ['ingreso', 'egreso'])) {
    //         throw new InvalidArgumentException('El tipo debe ser "ingreso" o "egreso".');
    //     }

    //     // Validar que las entidades relacionadas existan
    //     if ($dto->proyectoId && !$this->proyectoRepository->obtenerPorId($dto->proyectoId)) {
    //         throw new InvalidArgumentException('El proyecto especificado no existe.');
    //     }

    //     if ($dto->asociadoId && !$this->asociadoRepository->obtenerPorId($dto->asociadoId)) {
    //         throw new InvalidArgumentException('El asociado especificado no existe.');
    //     }

    //     if ($dto->proveedorId && !$this->proveedorRepository->obtenerPorId($dto->proveedorId)) {
    //         throw new InvalidArgumentException('El proveedor especificado no existe.');
    //     }

    //     if (!$this->organizacionRepository->obtenerPorId($dto->organizacionId)) {
    //         throw new InvalidArgumentException('La organización especificada no existe.');
    //     }
    // }

    // /**
    //  * Actualizar el monto actual de un proyecto basado en sus movimientos.
    //  *
    //  * @param int $proyectoId
    //  */
    // private function actualizarMontoProyecto(int $proyectoId): void
    // {
    //     $ingresos = $this->movimientoRepository->calcularTotalIngresosPorProyecto($proyectoId);
    //     $egresos = $this->movimientoRepository->calcularTotalEgresosPorProyecto($proyectoId);

    //     $montoActual = $ingresos - $egresos;

    //     $this->proyectoRepository->actualizar($proyectoId, ['monto_actual' => $montoActual]);
    // }
}
