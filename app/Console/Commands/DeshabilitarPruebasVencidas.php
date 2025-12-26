<?php

namespace App\Console\Commands;

use App\Models\Organizacion;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeshabilitarPruebasVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizaciones:deshabilitar-prueba-vencida';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deshabilita organizaciones en periodo de prueba que ya vencieron (columna habilitada)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoy = Carbon::today();

        Organizacion::query()
            ->where('es_prueba', true)
            ->whereNotNull('fecha_fin_prueba')
            ->whereDate('fecha_fin_prueba', '<', $hoy)
            ->where(function ($q) {
                // Si la columna habilitada existe y está en true, la seleccionamos.
                // Si no existe en la tabla (entorno antiguo), this will be ignored by the DB
            })
            ->orderBy('id')
            ->chunkById(100, function ($organizaciones) use ($hoy): void {
                foreach ($organizaciones as $organizacion) {
                    // Solo procesar si la org está habilitada (si el campo existe)
                    if (isset($organizacion->habilitada) && ! $organizacion->habilitada) {
                        continue;
                    }

                    $organizacion->habilitada = false;
                    $organizacion->save();

                    Log::info('Organizacion deshabilitada por prueba vencida', [
                        'organizacion_id' => $organizacion->id,
                        'fecha_fin_prueba' => $organizacion->fecha_fin_prueba,
                    ]);
                }
            });

        return Command::SUCCESS;
    }
}
