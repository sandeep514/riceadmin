<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenSqlQueryLogsRequestPath extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('sql_query_logs')) {
            return;
        }

        // Avoid doctrine/dbal requirement for ->change().
        DB::statement('ALTER TABLE `sql_query_logs` MODIFY `request_path` VARCHAR(1000) NULL');
    }

    public function down()
    {
        if (! Schema::hasTable('sql_query_logs')) {
            return;
        }

        DB::statement('ALTER TABLE `sql_query_logs` MODIFY `request_path` VARCHAR(255) NULL');
    }
}
