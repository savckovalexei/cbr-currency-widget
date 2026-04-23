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
        // Загрузка курсов каждый час
        $schedule->command('app:fetch-cbr-rates')->hourly();

        // Или в рабочее время по будням
    $schedule->command('app:fetch-cbr-rates')
        ->weekdays()
        ->between('10:00', '17:00')
        ->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
