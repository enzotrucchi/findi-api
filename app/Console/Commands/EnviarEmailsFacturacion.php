<?php

namespace App\Console\Commands;

use App\Models\Facturacion;
use App\Models\Organizacion;
use App\Mail\FacturacionMensual;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnviarEmailsFacturacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturacion:enviar-emails
        {--periodo= : Periodo en formato YYYY-MM (por defecto el mes actual)}
        {--save-html : Guarda el HTML del email en storage/app/mails (solo para debug)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía emails de facturación a organizaciones habilitadas con facturación pendiente';

    const PRECIO_UNITARIO = 2.00;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodo = $this->option('periodo') ?? now()->format('Y-m');

        $this->info("Enviando emails de facturación para el periodo: {$periodo}");
        $this->newLine();

        // Obtener facturaciones pendientes de pago del periodo actual, solo de organizaciones habilitadas
        $facturaciones = Facturacion::where('periodo', $periodo)
            ->pendientes()
            ->with('organizacion')
            ->get();

        if ($facturaciones->isEmpty()) {
            $this->info('No hay facturaciones pendientes para enviar.');
            return Command::SUCCESS;
        }

        $enviados = 0;
        $errores = 0;
        $detalles = [];

        foreach ($facturaciones as $facturacion) {
            try {
                // Verificar que la organización esté habilitada
                if (!$facturacion->organizacion->habilitada) {
                    $detalles[] = [
                        'organizacion_id' => $facturacion->organizacion->id,
                        'nombre' => $facturacion->organizacion->nombre,
                        'status' => 'skip',
                        'mensaje' => 'Organización deshabilitada',
                    ];
                    continue;
                }

                // Calcular monto
                $monto = $facturacion->monto ?? $facturacion->calcularMonto(self::PRECIO_UNITARIO);

                // Obtener email del contacto (ajustar según tu estructura)
                $email = $this->obtenerEmailOrganizacion($facturacion->organizacion);

                if (!$email) {
                    $detalles[] = [
                        'organizacion_id' => $facturacion->organizacion->id,
                        'nombre' => $facturacion->organizacion->nombre,
                        'status' => 'skip',
                        'mensaje' => 'No hay email registrado',
                    ];
                    continue;
                }

                $mailable = new FacturacionMensual($facturacion);

                if ($this->option('save-html')) {
                    // Renderizar vista del email
                    $emailHtml = view('emails.facturacion-mensual', [
                        'organizacion' => $facturacion->organizacion,
                        'monto' => $monto,
                        'periodoVisual' => $mailable->periodoVisual,
                        'cantidadAsociados' => $facturacion->cantidad_asociados,
                        'adminNombres' => $mailable->adminNombres,
                    ])->render();

                    // Guardar email como archivo HTML para visualización
                    $mailsPath = storage_path('app/mails');
                    if (!file_exists($mailsPath)) {
                        mkdir($mailsPath, 0755, true);
                    }
                    $safeName = Str::slug($facturacion->organizacion->nombre);
                    $periodoSlug = str_replace('-', '', $facturacion->periodo);
                    $filename = "facturacion_{$facturacion->organizacion->id}_{$safeName}_{$periodoSlug}.html";
                    file_put_contents("{$mailsPath}/{$filename}", $emailHtml);
                }

                // Enviar email
                Mail::to($email)->send($mailable);

                $enviados++;
                $detalles[] = [
                    'organizacion_id' => $facturacion->organizacion->id,
                    'nombre' => $facturacion->organizacion->nombre,
                    'email' => $email,
                    'monto' => '$' . number_format($monto, 2),
                    'status' => 'success',
                ];

                Log::info("Email de facturación enviado a {$email}", [
                    'organizacion_id' => $facturacion->organizacion->id,
                    'periodo' => $periodo,
                    'monto' => $monto,
                ]);
            } catch (\Exception $e) {
                $errores++;
                $detalles[] = [
                    'organizacion_id' => $facturacion->organizacion->id,
                    'nombre' => $facturacion->organizacion->nombre,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];

                Log::error("Error al enviar email de facturación", [
                    'organizacion_id' => $facturacion->organizacion->id,
                    'error' => $e->getMessage(),
                    'periodo' => $periodo,
                ]);
            }
        }

        $this->info("✓ Emails enviados: {$enviados}");
        $this->error("✗ Errores: {$errores}");
        $this->newLine();

        if (!empty($detalles)) {
            $this->table(
                ['Org ID', 'Nombre', 'Email', 'Monto', 'Estado'],
                collect($detalles)->map(function ($detalle) {
                    return [
                        $detalle['organizacion_id'],
                        $detalle['nombre'],
                        $detalle['email'] ?? 'N/A',
                        $detalle['monto'] ?? 'N/A',
                        $detalle['status'] === 'success' ? '✓' : ($detalle['status'] === 'skip' ? '⊘' : '✗'),
                    ];
                })
            );
        }

        $this->newLine();
        $this->info('Proceso completado.');

        return Command::SUCCESS;
    }

    /**
     * Obtener email de la organización.
     * Obtiene el email del asociado admin de la organización.
     *
     * @param Organizacion $organizacion
     * @return string|null
     */
    private function obtenerEmailOrganizacion(Organizacion $organizacion): ?string
    {
        // Obtener el asociado que es admin de esta organización
        $admin = $organizacion->asociados()
            ->wherePivot('es_admin', true)
            ->wherePivot('activo', true)
            ->first();

        return $admin?->email;
    }
}
