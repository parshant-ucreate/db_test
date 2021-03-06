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
            exec("pgbadger --prefix '%t [%p]: [%l-1] user=%u,db=%d,app=%a,client=%h ' /var/log/postgresql/postgresql-10-main.log  -o ".public_path()."/db_logs.html  2>&1", $output );
            \Log::info(date("Y-m-d H:i:s") .' database report output '.  implode(' ', $output) );
        })->everyTenMinutes();

        $database_list = DbList::where('backp_time','>',0)->get();
        foreach ($database_list as $key => $db) {
            $min = $hours = $db->backp_time;
            if($db->backup_type == 1){
                $schedule->job(new RunDatabaseBackup($db))->cron('0 */'.$hours.' * * *');
            }else{
                $schedule->job(new RunDatabaseBackup($db))->cron('*/'.$min.' * * * *');
            }
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
