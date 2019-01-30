<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use App\DbUser;

class DbList extends Model
{
    protected $table = 'database_list';

    protected $fillable = [
        'name'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at',
    ];
    
    public function dbUser() {
    	return $this->hasMany('App\DbUser', 'database_list_id', 'id')->select(['id', 'database_list_id', 'username', 'password', 'user_type']);
    }

    public function dbBackup() {
        return $this->hasMany('App\DbBackup', 'database_list_id', 'id')->select(['id', 'database_list_id', 'filename', 'type','created_at']);
    }

    public static function isDbExists($db_name) {
    	return static::where('name', $db_name)->pluck('id');
    }

    public static function getDbDetails($db_id) {
    	return static::where('id', $db_id)->with('dbUser')->get();
    }
}
