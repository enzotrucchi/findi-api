<?php

namespace App\Console\Commands;

use App\Services\FacturaService;
use Illuminate\Console\Command;

class MarcarFacturasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:marcar-vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca las facturas pendientes como vencidas y deshabilita las organizaciones correspondientes';

    /**
     * Execute the console command.
     */
    public function handle(FacturaService $facturaService)
    {
        $this->info('Marcando facturas vencidas...');
        $this->newLine();

        $facturasActualizadas = $facturaService->marcarFacturasVencidas();

        $this->info("✓ Facturas marcadas como vencidas: {$facturasActualizadas}");
        $this->newLine();

        $this->info('Deshabilitando organizaciones vencidas...');
        $this->newLine();

        $resultadoBloqueo = $facturaService->deshabilitarOrganizacionesVencidas();

        $this->info("✓ Organizaciones deshabilitadas: {$resultadoBloqueo['deshabilitadas']}");
        $this->newLine();

        $this->info('Proceso completado.');

        return Command::SUCCESS;
    }
}
