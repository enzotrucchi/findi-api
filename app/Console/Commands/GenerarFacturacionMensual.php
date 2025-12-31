<?php

namespace App\Console\Commands;

use App\Models\Organizacion;
use App\Models\Facturacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerarFacturacionMensual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturacion:generar-mensual {--periodo= : Periodo en formato YYYY-MM (por defecto el mes actual)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera registros de facturación mensual para todas las organizaciones activas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodo = $this->option('periodo') ?? now()->format('Y-m');

        $this->info("Generando facturación para el periodo: {$periodo}");
        $this->newLine();

        // Obtener todas las organizaciones habilitadas (no en prueba)
        $organizaciones = Organizacion::where('habilitada', true)
            ->where('es_prueba', false)->orWhere('es_prueba', null)
            ->get();

        $generadas = 0;
        $errores = 0;
        $detalles = [];

        foreach ($organizaciones as $organizacion) {
            try {
                // Verificar que no exista facturación para este periodo
                $facturacionExistente = Facturacion::where('organizacion_id', $organizacion->id)
                    ->where('periodo', $periodo)
                    ->first();

                if ($facturacionExistente) {
                    $detalles[] = [
                        'organizacion_id' => $organizacion->id,
                        'nombre' => $organizacion->nombre,
                        'status' => 'skip',
                        'mensaje' => 'Ya existe facturación para este periodo',
                    ];
                    continue;
                }

                // Congelar cantidad de asociados activos
                $cantidadAsociados = $organizacion->cantidadAsociadosActivos();

                // Calcular monto (cantidad_asociados * 2)
                $monto = $cantidadAsociados * 2;

                // Crear registro de facturación
                Facturacion::create([
                    'organizacion_id' => $organizacion->id,
                    'periodo' => $periodo,
                    'cantidad_asociados' => $cantidadAsociados,
                    'monto' => $monto,
                ]);

                $generadas++;
                $detalles[] = [
                    'organizacion_id' => $organizacion->id,
                    'nombre' => $organizacion->nombre,
                    'cantidad_asociados' => $cantidadAsociados,
                    'monto' => $monto,
                    'status' => 'success',
                ];

                Log::info("Facturación generada para organización {$organizacion->id}", [
                    'periodo' => $periodo,
                    'cantidad_asociados' => $cantidadAsociados,
                ]);
            } catch (\Exception $e) {
                $errores++;
                $detalles[] = [
                    'organizacion_id' => $organizacion->id,
                    'nombre' => $organizacion->nombre,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];

                Log::error("Error al generar facturación para organización {$organizacion->id}", [
                    'error' => $e->getMessage(),
                    'periodo' => $periodo,
                ]);
            }
        }

        $this->info("✓ Facturaciones generadas: {$generadas}");
        $this->error("✗ Errores: {$errores}");
        $this->newLine();

        if (!empty($detalles)) {
            $this->table(
                ['Org ID', 'Nombre', 'Asociados', 'Monto', 'Estado'],
                collect($detalles)->map(function ($detalle) {
                    return [
                        $detalle['organizacion_id'],
                        $detalle['nombre'],
                        $detalle['cantidad_asociados'] ?? 'N/A',
                        isset($detalle['monto']) ? '$' . number_format($detalle['monto'], 2) : 'N/A',
                        $detalle['status'] === 'success' ? '✓' : ($detalle['status'] === 'skip' ? '⊘' : '✗'),
                    ];
                })
            );
        }

        $this->newLine();
        $this->info('Proceso completado.');

        return Command::SUCCESS;
    }
}
