<?php

namespace App\Console;

use App\Util\LineWebhook\PushMessage;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\checkMessGivePoint::class,
        Commands\csvOutput::class,
        Commands\sendGivePoints::class,
        Commands\syncAccunixCoupon::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sendGivePoints')->everyMinute();
        $schedule->command('checkMessGivePoint')->everyTenMinutes();

        $schedule->command('csvOutput')->everyFiveMinutes();
        $schedule->command('syncAccunixCoupon')->everySixHours();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // $this->load(__DIR__.'/Commands');

        // require base_path('routes/console.php');
    }
}
