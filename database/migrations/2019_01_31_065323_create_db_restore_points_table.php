<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDbRestorePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_restore_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('database_list_id');
            $table->bigInteger('db_backup_id');
            $table->bigInteger('restore_point_id')->comment('last created backup_id before restore database'); 
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('database_list_id')->references('id')->on('database_list')->onDelete('cascade');
            $table->foreign('db_backup_id')->references('id')->on('db_backup')->onDelete('cascade');
            $table->foreign('restore_point_id')->references('id')->on('db_backup')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_restore_points');
    }
}
