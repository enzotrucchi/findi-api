<?php

namespace App\Mail;

use App\Models\Facturacion;
use App\Models\Organizacion;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturacionMensual extends Mailable
{
    use Queueable, SerializesModels;

    public string $periodoVisual;
    public Organizacion $organizacion;
    public float $monto;
    public string $adminNombres;

    /**
     * Create a new message instance.
     */
    public function __construct(public Facturacion $facturacion)
    {
        $this->organizacion = $facturacion->organizacion;
        $this->monto = (float) ($facturacion->monto ?? $facturacion->calcularMonto(2.00));

        $this->adminNombres = $this->organizacion
            ->asociados()
            ->wherePivot('es_admin', true)
            ->wherePivot('activo', true)
            ->pluck('nombre')
            ->filter()
            ->implode(', ');

        // $facturacion->periodo viene como YYYY-MM
        $this->periodoVisual = Carbon::createFromFormat('Y-m', $this->facturacion->periodo)->format('m/Y');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Facturación Mensual - {$this->periodoVisual}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.facturacion-mensual',
            with: [
                'organizacion' => $this->organizacion,
                'facturacion' => $this->facturacion,
                'monto' => $this->monto,
                'periodoVisual' => $this->periodoVisual,
                'cantidadAsociados' => $this->facturacion->cantidad_asociados,
                'adminNombres' => $this->adminNombres,
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
