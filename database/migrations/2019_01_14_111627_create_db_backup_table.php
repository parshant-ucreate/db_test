<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDbBackupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_backup', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('database_list_id');
            $table->string('filename');
            $table->string('type');  // auto|manual
            $table->timestamps();
            $table->foreign('database_list_id')->references('id')->on('database_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_backup');
    }
}
