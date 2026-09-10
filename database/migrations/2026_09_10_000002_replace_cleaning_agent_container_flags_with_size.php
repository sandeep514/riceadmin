<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceCleaningAgentContainerFlagsWithSize extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_cleaning_agent_products')) {
            return;
        }

        Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_cleaning_agent_products', 'container_size')) {
                $table->unsignedSmallInteger('container_size')->nullable()->after('user_id')->index();
            }
        });

        if (Schema::hasColumn('web_cleaning_agent_products', 'container_40_ft')) {
            DB::table('web_cleaning_agent_products')
                ->where('container_40_ft', 1)
                ->whereNull('container_size')
                ->update(['container_size' => 40]);
        }

        if (Schema::hasColumn('web_cleaning_agent_products', 'container_20_ft')) {
            // Legacy 20 FT rows: keep as null unless already set; product UI now uses 40/50.
            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                $table->dropColumn('container_20_ft');
            });
        }

        if (Schema::hasColumn('web_cleaning_agent_products', 'container_40_ft')) {
            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                $table->dropColumn('container_40_ft');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('web_cleaning_agent_products')) {
            return;
        }

        Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_cleaning_agent_products', 'container_20_ft')) {
                $table->unsignedTinyInteger('container_20_ft')->default(0)->after('user_id');
            }
            if (! Schema::hasColumn('web_cleaning_agent_products', 'container_40_ft')) {
                $table->unsignedTinyInteger('container_40_ft')->default(0)->after('container_20_ft');
            }
        });

        if (Schema::hasColumn('web_cleaning_agent_products', 'container_size')) {
            DB::table('web_cleaning_agent_products')
                ->where('container_size', 40)
                ->update(['container_40_ft' => 1]);

            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                $table->dropColumn('container_size');
            });
        }
    }
}
