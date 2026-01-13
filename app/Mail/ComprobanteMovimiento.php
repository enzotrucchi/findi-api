<?php

namespace App\Mail;

use App\Models\Movimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComprobanteMovimiento extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Movimiento $movimiento,
        public string $organizacionNombre,
        public ?string $pdfContent = null
    ) {
        $this->afterCommit(); // clave: se encola recién cuando la tx committeó
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tipo = ucfirst($this->movimiento->tipo);
        return new Envelope(
            subject: "Comprobante de {$tipo} - Findi",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.comprobante-movimiento',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfContent) {
            $fecha = \Carbon\Carbon::parse($this->movimiento->fecha)->format('Y-m-d');
            $tipo = $this->movimiento->tipo;
            $nombreArchivo = "comprobante_{$tipo}_{$fecha}.pdf";

            $attachments[] = Attachment::fromData(fn() => $this->pdfContent, $nombreArchivo)
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
