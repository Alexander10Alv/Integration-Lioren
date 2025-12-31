<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Procesar cola de sincronización cada 5 minutos
        $schedule->command('sync:process-queue')->everyFiveMinutes();
        
        // Detectar nuevas locations cada 6 horas
        $schedule->command('sync:detect-locations')->everySixHours();
        
        // Cerrar chats inactivos diariamente a las 2 AM
        $schedule->command('chats:close-inactive')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
