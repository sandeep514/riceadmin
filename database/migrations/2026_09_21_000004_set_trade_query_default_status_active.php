<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetTradeQueryDefaultStatusActive extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('trade_query_milestone3')) {
            return;
        }

        DB::statement("
            ALTER TABLE `trade_query_milestone3`
            MODIFY `status` INT NOT NULL DEFAULT 6
            COMMENT '3 => \"sold\", 2 => ''expired'' , 1 => ''Pending'',6=>''Active'',4=>''In-Process'',5=>''De-active'',11 => ''close'', 12=> ''hold'''
        ");
    }

    public function down()
    {
        if (! Schema::hasTable('trade_query_milestone3')) {
            return;
        }

        DB::statement("
            ALTER TABLE `trade_query_milestone3`
            MODIFY `status` INT NOT NULL DEFAULT 1
            COMMENT '3 => \"sold\", 2 => ''expired'' , 1 => ''Pending'',6=>''Active'',4=>''In-Process'',5=>''De-active'',11 => ''close'', 12=> ''hold'''
        ");
    }
}
