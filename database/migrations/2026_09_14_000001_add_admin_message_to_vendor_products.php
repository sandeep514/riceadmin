<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminMessageToVendorProducts extends Migration
{
    public function up()
    {
        foreach ($this->tables() as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'admin_message')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->text('admin_message')->nullable();
            });
        }
    }

    public function down()
    {
        foreach ($this->tables() as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'admin_message')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('admin_message');
            });
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return [
            'web_rice_bag_products',
            'web_carton_products',
            'web_cartoon_products',
            'web_cylinder_products',
            'web_lab_equipment_products',
            'web_machinery_equipment_products',
            'web_clearing_agent_products',
        ];
    }
}
