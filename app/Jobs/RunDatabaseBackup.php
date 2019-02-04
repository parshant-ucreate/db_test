<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\DbList;
use App\DbBackup;
use App\DbRestorePoints;
use Log;
use Illuminate\Support\Facades\Storage;

class RunDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $db = '';
    public $type = '';
    public $db_backup_id = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DbList $db , $type = 'auto', $db_backup_id = null )
    {
        $this->db = $db;
        $this->type = $type;
        $this->db_backup_id = $db_backup_id;
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
        Log::info('Dump Staring for '.$this->db->name);
        
        $fileName = $this->db->name.'_'.time().'.dump';
        
        exec('pg_dump -b -F c --dbname=postgresql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/'.$this->db->name.' > db_backup/'.$fileName .' 2>&1' ,$output);
         
        //save in to local database
        $backup = DbBackup::create(['filename' => $fileName, 'database_list_id' => $this->db->id, 'type' => $this->type ]);

        if($this->db_backup_id){
            DbRestorePoints::create([
                                'database_list_id' => $this->db->id,
                                'db_backup_id' => $this->db_backup_id,
                                'restore_point_id' => $backup->id,
                            ]);
        }

        //Move to S3
        $s3 = Storage::disk('s3');
        $filePath = $this->db->name.'/' . $fileName;
        $res = $s3->put($filePath, fopen('db_backup/'.$fileName, 'r+'));

        //remove file from server
        unlink('db_backup/'.$fileName);
        Log::info('Dump Completed for '.$this->db->name);

    }
}
