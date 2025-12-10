<?php

namespace App\Console;

use Log;
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
        // Check pending payments every 5 minutes
        $schedule->command('payments:check-pending')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Send payment reminders daily at 9 AM
        $schedule->command('payments:send-reminders')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->runInBackground();
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
