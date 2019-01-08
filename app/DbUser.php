<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class DbUser extends Model
{
    protected $table = 'database_users';

    protected $fillable = [
        'database_list_id', 'username', 'password', 'user_type'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at',
    ];
    
}
