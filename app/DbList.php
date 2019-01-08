<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class DbList extends Model
{
    protected $table = 'database_list';

    protected $fillable = [
        'name'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at',
    ];
    
}
