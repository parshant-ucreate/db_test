<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class DbUser extends Model
{	
	use SoftDeletes;
 	/**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $table = 'database_users';

    protected $fillable = [
        'database_list_id', 'username', 'password', 'user_type'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at',
    ];
    
}
