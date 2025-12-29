<?php

namespace App\Console\Commands;

use App\Mail\ReporteOrganizacionesProximas;
use App\Models\Organizacion;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class EnviarReporteOrganizacionesProximas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizaciones:reporte-proximas-a-vencer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un reporte diario de las organizaciones próximas a vencer (dentro de 3 días)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoy = Carbon::today();
        $en3Dias = Carbon::today()->addDays(3);

        // Obtener organizaciones que vencen dentro de 3 días
        $organizaciones = Organizacion::query()
            ->where('es_prueba', true)
            ->whereNotNull('fecha_fin_prueba')
            ->whereBetween('fecha_fin_prueba', [$hoy, $en3Dias])
            ->orderBy('fecha_fin_prueba', 'asc')
            ->get()
            ->map(function ($org) use ($hoy) {
                return [
                    'id' => $org->id,
                    'nombre' => $org->nombre,
                    'fecha_fin_prueba' => $org->fecha_fin_prueba,
                    'dias_restantes' => $hoy->diffInDays(Carbon::parse($org->fecha_fin_prueba), false),
                    'fecha_formateada' => Carbon::parse($org->fecha_fin_prueba)->format('d/m/Y'),
                ];
            });

        // Si hay organizaciones próximas a vencer, enviar reporte
        if ($organizaciones->isNotEmpty()) {
            Mail::send(new ReporteOrganizacionesProximas($organizaciones->toArray()));

            $this->info("Reporte enviado a trucchienzo@gmail.com con {$organizaciones->count()} organizaciones próximas a vencer.");
        } else {
            $this->info('No hay organizaciones próximas a vencer.');
        }

        return Command::SUCCESS;
    }
}
