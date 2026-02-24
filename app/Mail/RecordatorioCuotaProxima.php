<?php

namespace App\Mail;

use App\Models\Cuota;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecordatorioCuotaProxima extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public int $cuotaId) {}

    public function build()
    {
        $cuota = Cuota::with('planPago')->findOrFail($this->cuotaId);

        return $this->subject("Recordatorio de cuota #{$cuota->numero}")
            ->view('emails.recordatorio-cuota-proxima', [
                'cuota' => $cuota,
                'planPago' => $cuota->planPago,
            ]);
    }
}
