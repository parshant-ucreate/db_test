<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DbBackup extends Model
{
    protected $table = 'db_backup';

    protected $fillable = [
        'filename','type','database_list_id'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at',
    ];
}