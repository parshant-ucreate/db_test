<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\DbList;
use App\Jobs\RunDatabaseBackup;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
           exec("pgbadger --prefix '%t [%p]: [%l-1] user=%u,db=%d,app=%a,client=%h ' /var/log/postgresql/postgresql-10-main.log  -o ".public_path()."/db_logs.html");
        })->everyFifteenMinutes();

        $database_list = DbList::where('backp_time','>',0)->get();
        foreach ($database_list as $key => $db) {
            $schedule->job(new RunDatabaseBackup($db))->cron('*/'.$db->backp_time.' * * * *');
        }
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
