<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSqlQueryLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sql_query_logs')) {
            return;
        }

        Schema::create('sql_query_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source', 32)->default('app');
            $table->unsignedInteger('duration_ms')->default(0);
            $table->unsignedTinyInteger('cpu_percent')->nullable();
            $table->string('sql_hash', 32)->nullable();
            $table->text('sql_text');
            $table->string('db_name', 191)->nullable();
            $table->string('connection', 191)->nullable();
            $table->string('request_path', 255)->nullable();
            $table->unsignedBigInteger('mysql_thread_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
            $table->index(['duration_ms']);
            $table->index(['sql_hash', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sql_query_logs');
    }
}
