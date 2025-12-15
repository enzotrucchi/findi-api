<?php

namespace App\Services;

use App\DTOs\Movimiento\MovimientoDTO;
use App\DTOs\Movimiento\FiltroMovimientoDTO;
use App\Mail\ComprobanteMovimiento;
use App\Services\Traits\ObtenerOrganizacionSeleccionada;
use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Servicio de Movimientos
 * 
 * Contiene toda la lógica de negocio relacionada con movimientos.
 */
class MovimientoService
{
    use ObtenerOrganizacionSeleccionada;

    public function __construct() {}

    /**
     * Obtener colección paginada de movimientos.
     *
     * @param FiltroMovimientoDTO $filtroDTO
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function obtenerColeccion(FiltroMovimientoDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Movimiento::query();

        $query->with(['asociado', 'modoPago']);

        if ($filtroDTO->getFechaDesde()) {
            $query->where('fecha', '>=', $filtroDTO->getFechaDesde());
        }

        if ($filtroDTO->getFechaHasta()) {
            $query->where('fecha', '<=', $filtroDTO->getFechaHasta());
        }

        return $query
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(perPage: 10, columns: ['*'], pageName: 'pagina', page: $filtroDTO->getPagina());
    }

    /**
     * Obtener balance (sum de ingresos y egresos).
     *
     * @return array
     */
    public function obtenerBalance(): array
    {
        $query = Movimiento::query();

        $ingresos = (clone $query)->where('tipo', 'ingreso')->sum('monto');
        $egresos = (clone $query)->where('tipo', 'egreso')->sum('monto');

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance' => $ingresos - $egresos,
        ];
    }

    /**
     * Crear un nuevo movimiento.
     *
     * @param MovimientoDTO $dto
     * @return Movimiento
     * @throws InvalidArgumentException
     */
    public function crear(MovimientoDTO $dto): Movimiento
    {
        $orgId = $this->obtenerOrganizacionId();

        $movimiento = Movimiento::create([
            'fecha' => $dto->fecha,
            'hora' => $dto->hora ?? now()->format('H:i:s'),
            'detalle' => $dto->detalle ? trim($dto->detalle) : null,
            'monto' => $dto->monto,
            'tipo' => $dto->tipo,
            'status' => $dto->status ?? 'pendiente',
            'adjunto' => $dto->adjunto,
            'proyecto_id' => $dto->proyectoId,
            'asociado_id' => $dto->asociadoId,
            'proveedor_id' => $dto->proveedorId,
            'modo_pago_id' => $dto->modoPagoId,
            'organizacion_id' => $orgId,
        ]);

        $movimiento->fresh();

        $movimiento->load(['asociado', 'modoPago', 'proyecto', 'proveedor', 'organizacion']);

        // Enviar correo con comprobante si el asociado tiene email
        if ($movimiento->asociado && $movimiento->asociado->email) {
            try {
                $organizacionNombre = $movimiento->organizacion->nombre ?? 'Findi';
                Mail::to($movimiento->asociado->email)->send(
                    new ComprobanteMovimiento($movimiento, $organizacionNombre)
                );
            } catch (\Exception $e) {
                // Log del error pero no falla la creación del movimiento
                Log::error('Error al enviar email de comprobante: ' . $e->getMessage());
            }
        }

        return $movimiento;
    }

    /**
     * Actualizar un movimiento existente.
     *
     * @param int $id
     * @param MovimientoDTO $dto
     * @return Movimiento|null
     * @throws InvalidArgumentException
     */
    public function actualizar(int $id, MovimientoDTO $dto): ?Movimiento
    {
        $query = Movimiento::query();

        $movimiento = $query->find($id);

        if (!$movimiento) {
            return null;
        }

        $movimiento->update([
            'fecha' => $dto->fecha,
            'hora' => $dto->hora ?? $movimiento->hora,
            'detalle' => $dto->detalle ? trim($dto->detalle) : null,
            'monto' => $dto->monto,
            'tipo' => $dto->tipo,
            'status' => $dto->status ?? $movimiento->status,
            'adjunto' => $dto->adjunto,
            'proyecto_id' => $dto->proyectoId,
            'asociado_id' => $dto->asociadoId,
            'proveedor_id' => $dto->proveedorId,
            'modo_pago_id' => $dto->modoPagoId,
        ]);

        $movimiento->fresh();

        $movimiento->load(['asociado', 'modoPago']);

        return $movimiento;
    }

    /**
     * Eliminar un movimiento.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        $query = Movimiento::query();

        $movimiento = $query->find($id);

        if (!$movimiento) {
            return false;
        }

        return $movimiento->delete();
    }
}
