<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('organizaciones:recordatorio-prueba')->dailyAt('11:00');
        // Deshabilitar organizaciones cuya prueba ya venció
        $schedule->command('organizaciones:deshabilitar-prueba-vencida')->dailyAt('02:00');
        // Enviar reporte diario de organizaciones próximas a vencer
        $schedule->command('organizaciones:reporte-proximas-a-vencer')->dailyAt('11:00');

        // Sistema de facturación
        // Generar facturas mensuales el primer día de cada mes a las 00:00
        $schedule->command('facturas:generar-mensuales')->monthlyOn(1, '00:00');
        // Marcar facturas vencidas y bloquear organizaciones diariamente a las 01:00
        $schedule->command('facturas:marcar-vencidas')->dailyAt('01:00');

        // Sistema de facturación mensual
        // Generar registros de facturación el día 1 de cada mes a las 00:30
        $schedule->command('facturacion:generar-mensual')->monthlyOn(1, '01:00');
        // Enviar emails de facturación del día 1 al 5 de cada mes a las 09:00
        $schedule->command('facturacion:enviar-emails')->cron('0 9 1-5 * *');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
