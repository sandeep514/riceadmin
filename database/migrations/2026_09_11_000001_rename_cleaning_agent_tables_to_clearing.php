<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameCleaningAgentTablesToClearing extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cleaning_agent_particulars_map') && ! Schema::hasTable('clearing_agent_particulars_map')) {
            Schema::rename('cleaning_agent_particulars_map', 'clearing_agent_particulars_map');
        }

        if (Schema::hasTable('web_cleaning_agent_products') && ! Schema::hasTable('web_clearing_agent_products')) {
            Schema::rename('web_cleaning_agent_products', 'web_clearing_agent_products');
        }
    }

    public function down()
    {
        if (Schema::hasTable('clearing_agent_particulars_map') && ! Schema::hasTable('cleaning_agent_particulars_map')) {
            Schema::rename('clearing_agent_particulars_map', 'cleaning_agent_particulars_map');
        }

        if (Schema::hasTable('web_clearing_agent_products') && ! Schema::hasTable('web_cleaning_agent_products')) {
            Schema::rename('web_clearing_agent_products', 'web_cleaning_agent_products');
        }
    }
}
