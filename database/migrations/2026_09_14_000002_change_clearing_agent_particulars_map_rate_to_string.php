<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeClearingAgentParticularsMapRateToString extends Migration
{
    public function up()
    {
        $this->alterRateColumn('VARCHAR(255) NULL');
    }

    public function down()
    {
        $this->alterRateColumn('DECIMAL(12,2) NULL');
    }

    private function alterRateColumn(string $definition): void
    {
        foreach (['clearing_agent_particulars_map', 'cleaning_agent_particulars_map'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'rate')) {
                continue;
            }

            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `rate` {$definition}");
        }
    }
}
