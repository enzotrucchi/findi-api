<?php

namespace App\Console\Commands;

use App\Mail\ResumenMensual;
use App\Models\Organizacion;
use App\Services\ResumenMensualService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnviarResumenMensual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:enviar-mensual
        {--periodo= : Periodo en formato YYYY-MM (por defecto el mes anterior)}
        {--save-html : Guarda el HTML del email en storage/app/mails (solo para debug)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía resumen mensual con totalizadores a los asociados admins de cada organización';

    /**
     * Execute the console command.
     */
    public function handle(ResumenMensualService $resumenService)
    {
        $periodo = $this->option('periodo') ?? now()->subMonth()->format('Y-m');

        $this->info("Enviando resúmenes mensuales para el periodo: {$periodo}");
        $this->newLine();

        // Obtener organizaciones habilitadas y de producción
        $organizaciones = $resumenService->obtenerOrganizacionesParaResumen();

        if ($organizaciones->isEmpty()) {
            $this->info('No hay organizaciones habilitadas para enviar resumen.');
            return Command::SUCCESS;
        }

        $enviados = 0;
        $errores = 0;
        $detalles = [];

        foreach ($organizaciones as $organizacion) {
            try {
                // Obtener totalizadores del mes
                $totalizadores = $resumenService->obtenerTotalizadores($organizacion, $periodo);

                // Obtener asociados activos con email y nombre
                $asociados = $this->obtenerAsociadosActivos($organizacion);

                if ($asociados->isEmpty()) {
                    $detalles[] = [
                        'organizacion_id' => $organizacion->id,
                        'nombre' => $organizacion->nombre,
                        'status' => 'skip',
                        'mensaje' => 'No hay asociados con email registrado',
                    ];
                    continue;
                }

                // Enviar email a cada asociado con su nombre personalizado
                $emailsEnviados = [];
                foreach ($asociados as $asociado) {
                    try {
                        $mailable = new ResumenMensual(
                            $organizacion,
                            $totalizadores,
                            $asociado->nombre ?: 'Asociado'
                        );

                        if ($this->option('save-html')) {
                            // Renderizar vista del email
                            $emailHtml = view('emails.resumen-mensual', [
                                'organizacion' => $organizacion,
                                'totalizadores' => $totalizadores,
                                'nombreAsociado' => $asociado->nombre ?: 'Asociado',
                            ])->render();

                            // Guardar email como archivo HTML para visualización
                            $mailsPath = storage_path('app/mails');
                            if (!file_exists($mailsPath)) {
                                mkdir($mailsPath, 0755, true);
                            }
                            $safeName = Str::slug($organizacion->nombre);
                            $asociadoSlug = Str::slug($asociado->nombre);
                            $periodoSlug = str_replace('-', '', $periodo);
                            $filename = "resumen_mensual_{$organizacion->id}_{$safeName}_{$asociadoSlug}_{$periodoSlug}.html";
                            file_put_contents("{$mailsPath}/{$filename}", $emailHtml);
                        }

                        // Enviar email en cola (async)
                        Mail::to($asociado->email)->queue($mailable);
                        $enviados++;
                        $emailsEnviados[] = $asociado->email;
                    } catch (\Exception $e) {
                        Log::error("Error al enviar resumen a {$asociado->email}", [
                            'organizacion_id' => $organizacion->id,
                            'asociado_id' => $asociado->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $detalles[] = [
                    'organizacion_id' => $organizacion->id,
                    'nombre' => $organizacion->nombre,
                    'email' => implode(', ', $emailsEnviados),
                    'status' => 'success',
                ];

                Log::info("Resumen mensual enviado a {$asociados->count()} asociados", [
                    'organizacion_id' => $organizacion->id,
                    'periodo' => $periodo,
                    'emails' => $emailsEnviados,
                ]);
            } catch (\Exception $e) {
                $errores++;
                $detalles[] = [
                    'organizacion_id' => $organizacion->id,
                    'nombre' => $organizacion->nombre,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];

                Log::error("Error al enviar resumen mensual", [
                    'organizacion_id' => $organizacion->id,
                    'error' => $e->getMessage(),
                    'periodo' => $periodo,
                ]);
            }
        }

        $this->info("✓ Resúmenes enviados: {$enviados}");
        $this->error("✗ Errores: {$errores}");
        $this->newLine();

        if (!empty($detalles)) {
            $this->table(
                ['Org ID', 'Nombre', 'Email', 'Estado'],
                collect($detalles)->map(function ($detalle) {
                    return [
                        $detalle['organizacion_id'],
                        $detalle['nombre'],
                        $detalle['email'] ?? ($detalle['mensaje'] ?? 'N/A'),
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
     * Obtener asociados activos con email de la organización.
     *
     * @param Organizacion $organizacion
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function obtenerAsociadosActivos(Organizacion $organizacion)
    {
        // Obtener todos los asociados activos con email
        return $organizacion->asociados()
            ->wherePivot('activo', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'nombre', 'email']);
    }
}
