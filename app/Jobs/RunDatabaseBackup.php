<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\DbList;
use App\DbBackup;
use Illuminate\Support\Facades\Storage;

class RunDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!is_dir('db_backup/')) {
            $oldmask = umask(0);
            mkdir("db_backup", 0777);
            umask($oldmask);
        }

        $database_list = DbList::get()->toArray();

        foreach ($database_list as $key => $db) {
            $fileName = $db['name'].'_'.time().'.sql';
            exec('pg_dump --dbname=postgresql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/'.$db['name'].' > db_backup/'.$fileName .' 2>&1' ,$output);
             
            //save in to local database
            DbBackup::create(['filename' => $fileName, 'database_list_id' => $db['id'], 'type' => 'auto']);

            //Move to S3
            $s3 = Storage::disk('s3');
            $filePath = $db['name'].'/' . $fileName;
            $res = $s3->put($filePath, file_get_contents('db_backup/'.$fileName));

            //remove file from server
            unlink('db_backup/'.$fileName);
        }
    }
}
