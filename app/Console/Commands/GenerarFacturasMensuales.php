<?php

namespace App\Console\Commands;

use App\Services\FacturaService;
use Illuminate\Console\Command;

class GenerarFacturasMensuales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:generar-mensuales {--periodo= : Periodo en formato YYYY-MM (por defecto el mes actual)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera facturas mensuales para todas las organizaciones activas';

    /**
     * Execute the console command.
     */
    public function handle(FacturaService $facturaService)
    {
        $periodo = $this->option('periodo') ?? now()->format('Y-m');

        $this->info("Generando facturas para el periodo: {$periodo}");
        $this->newLine();

        $resultados = $facturaService->generarFacturasParaTodasLasOrganizaciones($periodo);

        $this->info("✓ Facturas generadas: {$resultados['generadas']}");
        $this->error("✗ Errores: {$resultados['errores']}");
        $this->newLine();

        if (!empty($resultados['detalles'])) {
            $this->table(
                ['Organización', 'Nombre', 'Factura ID', 'Monto', 'Estado'],
                collect($resultados['detalles'])->map(function ($detalle) {
                    return [
                        $detalle['organizacion_id'],
                        $detalle['organizacion_nombre'],
                        $detalle['factura_id'] ?? 'N/A',
                        isset($detalle['monto_total']) ? '$' . number_format($detalle['monto_total'], 2) : 'N/A',
                        $detalle['status'] === 'success' ? '✓' : '✗ ' . ($detalle['error'] ?? ''),
                    ];
                })
            );
        }

        $this->newLine();
        $this->info('Proceso completado.');

        return Command::SUCCESS;
    }
}
