<?php

namespace App\Mail;

use App\Models\Asociado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioPruebaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Asociado $asociado,
        public string $organizacionNombre,
        public int $diasRestantes,
        public string $fechaFin
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->diasRestantes === 0
            ? 'Tu periodo de prueba finaliza hoy'
            : 'Tu periodo de prueba finaliza en ' . $this->formatearDias($this->diasRestantes);

        return new Envelope(
            to: $this->asociado->email,
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-prueba-admin',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function formatearDias(int $dias): string
    {
        return $dias === 1 ? '1 día' : $dias . ' días';
    }
}
