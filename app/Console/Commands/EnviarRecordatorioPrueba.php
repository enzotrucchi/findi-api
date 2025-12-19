<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioPruebaAdmin;
use App\Models\Organizacion;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatorioPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizaciones:recordatorio-prueba';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un recordatorio diario del periodo de prueba a los admins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoy = Carbon::today();

        Organizacion::query()
            ->where('es_prueba', true)
            ->whereNotNull('fecha_fin_prueba')
            ->whereDate('fecha_fin_prueba', '>=', $hoy)
            ->orderBy('id')
            ->chunkById(100, function ($organizaciones) use ($hoy): void {
                foreach ($organizaciones as $organizacion) {
                    $fin = Carbon::parse($organizacion->fecha_fin_prueba)->startOfDay();
                    $diasRestantes = $hoy->diffInDays($fin, false);

                    if ($diasRestantes < 0) {
                        continue;
                    }

                    $admins = $organizacion->asociados()
                        ->wherePivot('es_admin', true)
                        ->wherePivot('activo', true)
                        ->get();

                    foreach ($admins as $admin) {
                        Mail::send(new RecordatorioPruebaAdmin(
                            asociado: $admin,
                            organizacionNombre: $organizacion->nombre,
                            diasRestantes: $diasRestantes,
                            fechaFin: $fin->format('d/m/Y')
                        ));
                    }
                }
            });

        return Command::SUCCESS;
    }
}
