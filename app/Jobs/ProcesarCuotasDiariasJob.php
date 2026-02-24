<?php

namespace App\Jobs;

use App\Mail\RecordatorioCuotaProxima;
use App\Models\Cuota;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcesarCuotasDiariasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $hoy = now()->startOfDay();
        $fechaRecordatorio = now()->addDays(2)->toDateString();

        Cuota::query()
            ->where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', '<', $hoy->toDateString())
            ->update(['estado' => 'vencida']);

        Cuota::query()
            ->with('planPago.asociado')
            ->where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', $fechaRecordatorio)
            ->whereNull('recordatorio_enviado_at')
            ->orderBy('id')
            ->chunkById(100, function ($cuotas): void {
                foreach ($cuotas as $cuota) {
                    $email = $cuota->planPago?->asociado?->email;

                    if ($email) {
                        Mail::to($email)->queue(new RecordatorioCuotaProxima($cuota->id));
                    }

                    $cuota->update([
                        'recordatorio_enviado_at' => now(),
                    ]);
                }
            });
    }
}
