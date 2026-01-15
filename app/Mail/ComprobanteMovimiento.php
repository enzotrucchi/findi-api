<?php

namespace App\Mail;

use App\Models\Movimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComprobanteMovimiento extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $movimientoId,
        public string $organizacionNombre
    ) {
        $this->afterCommit();
    }

    public function build()
    {
        $movimiento = Movimiento::with(['asociado', 'modoPago', 'proyecto', 'proveedor', 'organizacion'])
            ->findOrFail($this->movimientoId);

        $organizacionNombre = $movimiento->organizacion->nombre ?? $this->organizacionNombre ?? 'Findi';

        $pdf = Pdf::loadView('pdf.comprobante-movimiento', [
            'movimiento' => $movimiento,
            'organizacionNombre' => $organizacionNombre,
        ]);

        return $this->subject("Comprobante de movimiento #{$movimiento->id}")
            ->view('emails.comprobante-movimiento', [
                'movimiento' => $movimiento,
                'organizacionNombre' => $organizacionNombre,
            ])
            ->attachData($pdf->output(), "comprobante_{$movimiento->id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
