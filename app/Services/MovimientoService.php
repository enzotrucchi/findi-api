<?php

namespace App\Services;

use App\DTOs\Movimiento\MovimientoDTO;
use App\DTOs\Movimiento\FiltroMovimientoDTO;
use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio de Movimientos
 * 
 * Contiene toda la lógica de negocio relacionada con movimientos.
 */
class MovimientoService
{
    public function __construct() {}

    /**
     * Obtener colección paginada de movimientos.
     *
     * @param FiltroMovimientoDTO $filtroDTO
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function obtenerColeccion(FiltroMovimientoDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Movimiento::query()
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(perPage: 15, columns: ['*'], pageName: 'pagina');
    }

    /**
     * Obtener balance (sum de ingresos y egresos).
     *
     * @return array
     */
    public function obtenerBalance(): array
    {
        $ingresos = Movimiento::where('tipo', 'ingreso')->sum('monto');
        $egresos = Movimiento::where('tipo', 'egreso')->sum('monto');

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
        $user = Auth::user();

        if (!$user) {
            abort(401, 'No autenticado.');
        }

        $orgId = $user->organizacion_seleccionada_id;

        if (!$orgId) {
            abort(403, 'No hay organización seleccionada.');
        }

        return Movimiento::create([
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
        $movimiento = Movimiento::find($id);

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

        return $movimiento->fresh();
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
}
