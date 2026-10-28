<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * If app_settings was created with market_mode only, add override columns.
 */
class AddMarketOverrideToAppSettings extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'market_override_status')) {
                $table->unsignedTinyInteger('market_override_status')->nullable()->after('market_to');
            }
            if (! Schema::hasColumn('app_settings', 'market_override_until')) {
                $table->timestamp('market_override_until')->nullable()->after('market_override_status');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('app_settings', 'market_override_status')) {
                $cols[] = 'market_override_status';
            }
            if (Schema::hasColumn('app_settings', 'market_override_until')) {
                $cols[] = 'market_override_until';
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
}
