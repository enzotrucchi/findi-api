<?php

namespace App\Mail;

use App\Models\Organizacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResumenMensual extends Mailable
{
    use Queueable, SerializesModels;

    public Organizacion $organizacion;
    public array $totalizadores;
    public string $nombreAsociado;

    /**
     * Create a new message instance.
     */
    public function __construct(Organizacion $organizacion, array $totalizadores, string $nombreAsociado)
    {
        $this->organizacion = $organizacion;
        $this->totalizadores = $totalizadores;
        $this->nombreAsociado = $nombreAsociado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Resumen Mensual - {$this->totalizadores['periodo_visual']}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resumen-mensual',
            with: [
                'organizacion' => $this->organizacion,
                'totalizadores' => $this->totalizadores,
                'nombreAsociado' => $this->nombreAsociado,
            ],
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
}
