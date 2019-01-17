<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultBackupTimeColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('database_list', function (Blueprint $table) {
            $table->integer('backp_time')->default(env('BACKUP_DEFAULT_TIME',0))->comment('time in minutes like 5 or 60'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('database_list', function (Blueprint $table) {
            $table->dropColumn('backp_time');  
        });
    }
}
