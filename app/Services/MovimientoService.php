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
    public function obtenerColeccion(FiltroMovimientoDTO $filtroDTO): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Movimiento::query();

        $query->with(['asociado', 'modoPago', 'proyecto', 'proveedor']);

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

                // Generar PDF en memoria
                $pdfContent = null;
                try {
                    $pdfContent = $this->generarPdfComprobante($movimiento);
                } catch (\Exception $e) {
                    Log::error('Error al generar PDF de comprobante: ' . $e->getMessage());
                }

                Mail::to($movimiento->asociado->email)->send(
                    new ComprobanteMovimiento($movimiento, $organizacionNombre, $pdfContent)
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
