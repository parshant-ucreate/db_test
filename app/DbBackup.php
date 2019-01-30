<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DbBackup extends Model
{
	use SoftDeletes;
	
    protected $table = 'db_backup';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'filename','type','database_list_id'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function Db() {
        return $this->belongsTo('App\DbList', 'database_list_id')->select(['id', 'name']);
    }

    public static function backupExist($filename) {
        return static::whereFilename($filename)->first();
    }
}