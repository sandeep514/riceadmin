<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateWebAccessTableForMenu extends Migration
{
    public function up()
    {
        // Check if route_name column exists
        $columns = DB::select("SHOW COLUMNS FROM `web_access` LIKE 'route_name'");
        
        if (!empty($columns)) {
            // Column exists, proceed with migration
            
            // First, drop the foreign key on role_id temporarily
            try {
                Schema::table('web_access', function (Blueprint $table) {
                    $table->dropForeign(['role_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or have different name
            }
            
            // Now drop the index if it exists
            $indexExists = DB::select("SHOW INDEX FROM `web_access` WHERE Key_name = 'web_access_role_id_category_id_plan_id_route_name_index'");
            if (!empty($indexExists)) {
                DB::statement('ALTER TABLE `web_access` DROP INDEX `web_access_role_id_category_id_plan_id_route_name_index`');
            }
            
            // Drop the route_name column
            Schema::table('web_access', function (Blueprint $table) {
                $table->dropColumn('route_name');
            });
        }
        
        // Check if web_side_menu_id column already exists
        $menuColumnExists = DB::select("SHOW COLUMNS FROM `web_access` LIKE 'web_side_menu_id'");
        
        if (empty($menuColumnExists)) {
            // Add new column if it doesn't exist
            Schema::table('web_access', function (Blueprint $table) {
                $table->unsignedBigInteger('web_side_menu_id')->nullable()->after('plan_id');
            });
        }
        
        // Re-add the role_id foreign key if it was dropped
        if (!empty($columns)) {
            try {
                Schema::table('web_access', function (Blueprint $table) {
                    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        }
        
        // Add foreign key for web_side_menu_id if it doesn't exist
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'web_access' AND COLUMN_NAME = 'web_side_menu_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if (empty($foreignKeys)) {
            Schema::table('web_access', function (Blueprint $table) {
                $table->foreign('web_side_menu_id')->references('id')->on('web_side_menu')->onDelete('cascade');
            });
        }
        
        // Add new index if it doesn't exist
        $newIndexExists = DB::select("SHOW INDEX FROM `web_access` WHERE Key_name = 'web_access_role_category_plan_menu_index'");
        if (empty($newIndexExists)) {
            Schema::table('web_access', function (Blueprint $table) {
                $table->index(['role_id', 'category_id', 'plan_id', 'web_side_menu_id'], 'web_access_role_category_plan_menu_index');
            });
        }
    }

    public function down()
    {
        // Drop foreign keys and index
        Schema::table('web_access', function (Blueprint $table) {
            $table->dropForeign(['web_side_menu_id']);
            $table->dropForeign(['role_id']);
            $table->dropIndex('web_access_role_category_plan_menu_index');
        });
        
        // Drop column
        Schema::table('web_access', function (Blueprint $table) {
            $table->dropColumn('web_side_menu_id');
        });
        
        // Add back route_name and index
        Schema::table('web_access', function (Blueprint $table) {
            $table->string('route_name', 255)->after('plan_id');
            $table->index(['role_id', 'category_id', 'plan_id', 'route_name'], 'web_access_role_id_category_id_plan_id_route_name_index');
        });
        
        // Re-add role_id foreign key
        Schema::table('web_access', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }
}

