<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackupTypeColumnInDatabaseListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('database_list', function (Blueprint $table) {
            $table->integer('backup_type')->default(0)->comment('type of backup 0 = Minutes , 1 = Hourly'); 
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
            $table->dropColumn('backup_type');  
        });
    }
}
