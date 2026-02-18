<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MovimientoEliminado extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $movimientoData,
        public string $organizacionNombre
    ) {}

    public function build()
    {
        return $this->subject("Notificación: Movimiento eliminado - {$this->organizacionNombre}")
            ->view('emails.movimiento-eliminado', [
                'movimiento' => $this->movimientoData,
                'organizacionNombre' => $this->organizacionNombre,
            ]);
    }
}
