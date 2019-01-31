<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DbRestorePoints extends Model
{
	use SoftDeletes;
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    protected $table = 'db_restore_points';

    protected $fillable = ['database_list_id','db_backup_id','restore_point_id'];
    
    protected $hidden = ['updated_at'];
}