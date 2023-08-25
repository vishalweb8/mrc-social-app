<?php

namespace App\Console;

use Arcanedev\LogViewer\Commands\Command;
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
        Commands\SendNewsletterToSubscribers::class,
        Commands\SendNotification::class,
        Commands\SearchTerms::class,
        Commands\VerifyPayment::class,
        Commands\BusinessSlugGenerate::class,
        Commands\InsertDataInLocations::class,
        Commands\sendSiteActivityNotification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('sendNewsletterToSubscribers')->everyMinute();
        //$schedule->command('sendNotification')->everyMinute();
        //$schedule->command('searchTerms')->weekly();
        // if (!strstr(shell_exec('ps xf'), 'php artisan queue:work')) {
		//     $schedule->command('queue:work --tries=3')->everyMinute()->withoutOverlapping();
        // }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
