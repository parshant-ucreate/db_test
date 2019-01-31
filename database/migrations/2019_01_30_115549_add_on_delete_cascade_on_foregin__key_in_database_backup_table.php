<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnDeleteCascadeOnForeginKeyInDatabaseBackupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('db_backup', function (Blueprint $table) {
            $table->dropForeign('db_backup_database_list_id_foreign');
            $table->foreign('database_list_id')->references('id')->on('database_list')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('db_backup', function (Blueprint $table) {
            $table->dropForeign('db_backup_database_list_id_foreign');
            $table->foreign('database_list_id')->references('id')->on('database_list');
        });
    }
}
