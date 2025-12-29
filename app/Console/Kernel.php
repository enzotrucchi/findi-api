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
        $schedule->command('organizaciones:deshabilitar-prueba-vencida')->dailyAt('00:05');
        // Enviar reporte diario de organizaciones próximas a vencer
        $schedule->command('organizaciones:reporte-proximas-a-vencer')->dailyAt('11:00');
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
