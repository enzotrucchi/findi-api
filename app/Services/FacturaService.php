<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Organizacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturaService
{
    /**
     * Precio unitario por asociado (en USD).
     * Puede ser configurado o cambiado según necesidad.
     */
    const PRECIO_UNITARIO_DEFAULT = 2.00;

    /**
     * Días de gracia antes del vencimiento.
     */
    const DIAS_VENCIMIENTO = 5;

    /**
     * Generar factura para una organización en un periodo específico.
     *
     * @param Organizacion $organizacion
     * @param string|null $periodo Formato YYYY-MM, si es null usa el mes actual
     * @param float|null $precioUnitario Si es null usa el precio default
     * @return Factura
     */
    public function generarFactura(
        Organizacion $organizacion,
        ?string $periodo = null,
        ?float $precioUnitario = null
    ): Factura {
        // Si no se especifica periodo, usar el mes actual
        if (!$periodo) {
            $periodo = now()->format('Y-m');
        }

        // Validar que no exista factura para este periodo
        $facturaExistente = Factura::where('organizacion_id', $organizacion->id)
            ->where('periodo', $periodo)
            ->first();

        if ($facturaExistente) {
            throw new \Exception("Ya existe una factura para el periodo {$periodo}");
        }

        // Congelar la cantidad de asociados activos
        $cantidadAsociados = $organizacion->cantidadAsociadosActivos();

        // Usar precio unitario especificado o el default
        $precioUnitario = $precioUnitario ?? self::PRECIO_UNITARIO_DEFAULT;

        // Calcular monto total
        $montoTotal = $cantidadAsociados * $precioUnitario;

        // Fecha de corte es hoy
        $fechaCorte = now();

        // Fecha de vencimiento: inicio del próximo mes + días de gracia
        $fechaVencimiento = Carbon::parse($periodo . '-01')
            ->addMonth()
            ->addDays(self::DIAS_VENCIMIENTO);

        // Crear la factura
        $factura = Factura::create([
            'organizacion_id' => $organizacion->id,
            'periodo' => $periodo,
            'fecha_corte' => $fechaCorte,
            'cantidad_asociados' => $cantidadAsociados,
            'precio_unitario' => $precioUnitario,
            'monto_total' => $montoTotal,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => Factura::ESTADO_PENDING,
        ]);

        Log::info("Factura generada para organización {$organizacion->id}", [
            'factura_id' => $factura->id,
            'periodo' => $periodo,
            'monto_total' => $montoTotal,
        ]);

        return $factura;
    }

    /**
     * Generar facturas para todas las organizaciones activas.
     *
     * @param string|null $periodo
     * @return array
     */
    public function generarFacturasParaTodasLasOrganizaciones(?string $periodo = null): array
    {
        if (!$periodo) {
            $periodo = now()->format('Y-m');
        }

        $organizaciones = Organizacion::where('es_prueba', false)
            ->where('habilitada', true)
            ->get();

        $resultados = [
            'generadas' => 0,
            'errores' => 0,
            'detalles' => [],
        ];

        foreach ($organizaciones as $organizacion) {
            try {
                $factura = $this->generarFactura($organizacion, $periodo);
                $resultados['generadas']++;
                $resultados['detalles'][] = [
                    'organizacion_id' => $organizacion->id,
                    'organizacion_nombre' => $organizacion->nombre,
                    'factura_id' => $factura->id,
                    'monto_total' => $factura->monto_total,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $resultados['errores']++;
                $resultados['detalles'][] = [
                    'organizacion_id' => $organizacion->id,
                    'organizacion_nombre' => $organizacion->nombre,
                    'error' => $e->getMessage(),
                    'status' => 'error',
                ];
                Log::error("Error al generar factura para organización {$organizacion->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $resultados;
    }

    /**
     * Procesar el pago de una factura.
     *
     * @param Factura $factura
     * @return void
     */
    public function procesarPago(Factura $factura): void
    {
        DB::transaction(function () use ($factura) {
            // Marcar factura como pagada
            $factura->marcarComoPagada();

            // Actualizar fecha de vencimiento de la organización
            $organizacion = $factura->organizacion;

            // La fecha de vencimiento será el último día del periodo facturado
            $fechaVencimiento = Carbon::parse($factura->periodo . '-01')
                ->endOfMonth();

            $organizacion->update([
                'fecha_vencimiento' => $fechaVencimiento,
                'habilitada' => true,
            ]);

            Log::info("Pago procesado para factura {$factura->id}", [
                'organizacion_id' => $organizacion->id,
                'nuevo_vencimiento' => $fechaVencimiento->format('Y-m-d'),
            ]);
        });
    }

    /**
     * Marcar facturas vencidas como expired.
     *
     * @return int Cantidad de facturas actualizadas
     */
    public function marcarFacturasVencidas(): int
    {
        $facturasVencidas = Factura::where('estado', Factura::ESTADO_PENDING)
            ->where('fecha_vencimiento', '<', now())
            ->get();

        $contador = 0;

        foreach ($facturasVencidas as $factura) {
            $factura->marcarComoVencida();

            // Deshabilitar la organización
            $organizacion = $factura->organizacion;
            if ($organizacion->estaHabilitada()) {
                $organizacion->deshabilitar();
                Log::warning("Organización {$organizacion->id} deshabilitada por factura vencida", [
                    'factura_id' => $factura->id,
                    'periodo' => $factura->periodo,
                ]);
            }

            $contador++;
        }

        return $contador;
    }

    /**
     * Obtener facturas pendientes de una organización.
     *
     * @param Organizacion $organizacion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerFacturasPendientes(Organizacion $organizacion)
    {
        return $organizacion->facturas()
            ->pendientes()
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Obtener el historial de facturas de una organización.
     *
     * @param Organizacion $organizacion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerHistorialFacturas(Organizacion $organizacion)
    {
        return $organizacion->facturas()
            ->orderBy('periodo', 'desc')
            ->get();
    }

    /**
     * Cancelar una factura (por ejemplo, si la organización se dio de baja).
     *
     * @param Factura $factura
     * @param string|null $motivo
     * @return void
     */
    public function cancelarFactura(Factura $factura, ?string $motivo = null): void
    {
        if ($factura->estaPagada()) {
            throw new \Exception("No se puede cancelar una factura que ya fue pagada");
        }

        $factura->cancelar();

        Log::info("Factura cancelada", [
            'factura_id' => $factura->id,
            'organizacion_id' => $factura->organizacion_id,
            'periodo' => $factura->periodo,
            'motivo' => $motivo,
        ]);
    }

    /**
     * Verificar y deshabilitar organizaciones vencidas.
     *
     * @return array
     */
    public function deshabilitarOrganizacionesVencidas(): array
    {
        $organizaciones = Organizacion::where('habilitada', true)
            ->where('es_prueba', false)
            ->where('fecha_vencimiento', '<', now())
            ->get();

        $deshabilitadas = 0;

        foreach ($organizaciones as $organizacion) {
            $organizacion->deshabilitar();
            $deshabilitadas++;

            Log::warning("Organización deshabilitada por vencimiento", [
                'organizacion_id' => $organizacion->id,
                'fecha_vencimiento' => $organizacion->fecha_vencimiento->format('Y-m-d'),
            ]);
        }

        return [
            'deshabilitadas' => $deshabilitadas,
        ];
    }
}
