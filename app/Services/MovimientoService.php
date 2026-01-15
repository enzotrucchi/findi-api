<?php

namespace App\Services;

use App\DTOs\Movimiento\MovimientoDTO;
use App\DTOs\Movimiento\FiltroMovimientoDTO;
use App\Mail\ComprobanteMovimiento;
use App\Services\Traits\ObtenerOrganizacionSeleccionada;
use App\Models\Movimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
    // public function obtenerColeccion(FiltroMovimientoDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    // {
    //     $query = Movimiento::query();

    //     $query->with(['asociado', 'modoPago', 'proyecto', 'proveedor']);

    //     if ($filtroDTO->getFechaDesde()) {
    //         $query->where('fecha', '>=', $filtroDTO->getFechaDesde());
    //     }

    //     if ($filtroDTO->getFechaHasta()) {
    //         $query->where('fecha', '<=', $filtroDTO->getFechaHasta());
    //     }

    //     return $query
    //         ->orderBy('fecha', 'desc')
    //         ->orderBy('hora', 'desc')
    //         ->paginate(perPage: 10, columns: ['*'], pageName: 'pagina', page: $filtroDTO->getPagina());
    // }
    public function obtenerColeccion(FiltroMovimientoDTO $filtroDTO): array
    {
        // Base query: ya viene filtrada por org gracias al scope
        $baseQuery = Movimiento::query();

        // ===== KPIs GLOBAL (sin filtros) =====
        $kpisGlobal = $this->calcularKpis(clone $baseQuery);

        // ===== KPIs FILTRADOS (con filtros) =====
        $kpiFiltradosQuery = clone $baseQuery;
        $this->aplicarFiltros($kpiFiltradosQuery, $filtroDTO);
        $kpisFiltrados = $this->calcularKpis($kpiFiltradosQuery);

        // ===== TABLA (con filtros + relaciones + paginación) =====
        $tableQuery = clone $baseQuery;
        $tableQuery->with(['asociado', 'modoPago', 'proyecto', 'proveedor']);
        $this->aplicarFiltros($tableQuery, $filtroDTO);

        $paginacion = $tableQuery
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(perPage: 10, columns: ['*'], pageName: 'pagina', page: $filtroDTO->getPagina());

        return [
            'kpis' => [
                'global' => $kpisGlobal,
                'filtrados' => $kpisFiltrados,
            ],
            'paginacion' => $paginacion,
        ];
    }

    private function aplicarFiltros($query, FiltroMovimientoDTO $filtroDTO): void
    {
        if ($filtroDTO->getFechaDesde()) {
            $query->whereDate('fecha', '>=', $filtroDTO->getFechaDesde());
        }

        if ($filtroDTO->getFechaHasta()) {
            $query->whereDate('fecha', '<=', $filtroDTO->getFechaHasta());
        }
    }

    private function calcularKpis($query): array
    {
        $row = $query->selectRaw("
        COUNT(*) as total_movimientos,
        SUM(CASE WHEN tipo = 'ingreso' THEN 1 ELSE 0 END) as ingresos_count,
        SUM(CASE WHEN tipo = 'egreso' THEN 1 ELSE 0 END) as egresos_count,
        SUM(CASE WHEN tipo = 'inicial' THEN 1 ELSE 0 END) as inicial_count,
        SUM(CASE WHEN status = 'pendiente' THEN 1 ELSE 0 END) as pendientes_count,
        COALESCE(SUM(CASE WHEN tipo IN ('ingreso','inicial') THEN monto ELSE 0 END),0) as ingresos_sum,
        COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END),0) as egresos_sum
    ")->first();

        $ingresos = (float) ($row->ingresos_sum ?? 0);
        $egresos  = (float) ($row->egresos_sum ?? 0);

        return [
            'total_movimientos' => (int) ($row->total_movimientos ?? 0),
            'ingresos_count' => (int) ($row->ingresos_count ?? 0),
            'egresos_count' => (int) ($row->egresos_count ?? 0),
            'inicial_count' => (int) ($row->inicial_count ?? 0),
            'pendientes_count' => (int) ($row->pendientes_count ?? 0),
            'ingresos_sum' => $ingresos,
            'egresos_sum' => $egresos,
            'saldo' => $ingresos - $egresos,
        ];
    }


    /**
     * Obtener balance (sum de ingresos y egresos).
     *
     * @return array
     */
    public function obtenerBalance(): array
    {
        $query = Movimiento::query();

        $inicial = (clone $query)->where('tipo', 'inicial')->sum('monto');
        $ingresos = (clone $query)->where('tipo', 'ingreso')->sum('monto');
        $egresos = (clone $query)->where('tipo', 'egreso')->sum('monto');

        return [
            'inicial' => $inicial,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance' => $inicial + $ingresos - $egresos,
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

                Mail::to($movimiento->asociado->email)->queue(
                    new ComprobanteMovimiento($movimiento->id, $organizacionNombre)
                );
            } catch (\Exception $e) {
                Log::error('Error al enviar email de comprobante: ' . $e->getMessage());
            }
        }


        return $movimiento;
    }

    /**
     * Carga masiva de movimientos desde un array de DTOs.
     */
    public function cargaMasiva(array $dtos): array
    {
        return DB::transaction(function () use ($dtos) {
            $movimientosCreados = [];

            foreach ($dtos as $dto) {
                $movimientosCreados[] = $this->crear($dto);
            }

            return $movimientosCreados;
        });
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

        $movimiento->load(['asociado', 'modoPago', 'proyecto', 'proveedor']);

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

    /**
     * Generar PDF del comprobante de movimiento en memoria.
     *
     * @param Movimiento $movimiento
     * @return string|null Contenido binario del PDF generado
     */
    private function generarPdfComprobante(Movimiento $movimiento): ?string
    {
        try {
            $organizacionNombre = $movimiento->organizacion->nombre ?? 'Findi';

            // Generar el PDF usando la vista
            $pdf = Pdf::loadView('pdf.comprobante-movimiento', [
                'movimiento' => $movimiento,
                'organizacionNombre' => $organizacionNombre
            ]);

            // Retornar el contenido binario del PDF
            return $pdf->output();
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar y descargar PDF del comprobante de un movimiento.
     *
     * @param int $id
     * @return \Illuminate\Http\Response|null
     */
    public function descargarComprobante(int $id)
    {
        $query = Movimiento::query();
        $movimiento = $query->with(['asociado', 'modoPago', 'proyecto', 'proveedor', 'organizacion'])->find($id);

        if (!$movimiento) {
            return null;
        }

        $organizacionNombre = $movimiento->organizacion->nombre ?? 'Findi';

        $pdf = Pdf::loadView('pdf.comprobante-movimiento', [
            'movimiento' => $movimiento,
            'organizacionNombre' => $organizacionNombre
        ]);

        $fecha = \Carbon\Carbon::parse($movimiento->fecha)->format('Y-m-d');
        $tipo = $movimiento->tipo;
        $nombreArchivo = "comprobante_{$tipo}_{$movimiento->id}_{$fecha}.pdf";

        return $pdf->download($nombreArchivo);
    }
}
