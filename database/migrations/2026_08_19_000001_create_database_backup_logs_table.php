<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseBackupLogsTable extends Migration
{
    public function up()
    {
        Schema::create('database_backup_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('filename', 255)->nullable();
            $table->timestamp('downloaded_at');
            $table->timestamps();

            $table->index('downloaded_at');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('database_backup_logs');
    }
}
