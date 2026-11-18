<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schedule windows (IST) for rice sourcing + paddy markets,
 * mirroring live-prices market_from / market_to + temporary overrides.
 */
class AddRiceSourcingAndPaddyMarketTimingToAppSettings extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'rice_sourcing_from')) {
                $table->string('rice_sourcing_from', 5)->default('09:00')->after('market_to');
            }
            if (! Schema::hasColumn('app_settings', 'rice_sourcing_to')) {
                $table->string('rice_sourcing_to', 5)->default('18:00')->after('rice_sourcing_from');
            }
            if (! Schema::hasColumn('app_settings', 'rice_sourcing_override_status')) {
                $table->unsignedSmallInteger('rice_sourcing_override_status')->nullable()->after('rice_sourcing_to');
            }
            if (! Schema::hasColumn('app_settings', 'rice_sourcing_override_until')) {
                $table->timestamp('rice_sourcing_override_until')->nullable()->after('rice_sourcing_override_status');
            }

            if (! Schema::hasColumn('app_settings', 'paddy_market_from')) {
                $table->string('paddy_market_from', 5)->default('09:00')->after('rice_sourcing_override_until');
            }
            if (! Schema::hasColumn('app_settings', 'paddy_market_to')) {
                $table->string('paddy_market_to', 5)->default('18:00')->after('paddy_market_from');
            }
            if (! Schema::hasColumn('app_settings', 'paddy_market_override_status')) {
                $table->unsignedSmallInteger('paddy_market_override_status')->nullable()->after('paddy_market_to');
            }
            if (! Schema::hasColumn('app_settings', 'paddy_market_override_until')) {
                $table->timestamp('paddy_market_override_until')->nullable()->after('paddy_market_override_status');
            }
        });

        // Backfill defaults from live market window when present.
        $row = DB::table('app_settings')->orderBy('id')->first();
        if ($row) {
            $from = $row->market_from ?? '09:00';
            $to = $row->market_to ?? '18:00';
            DB::table('app_settings')->where('id', $row->id)->update([
                'rice_sourcing_from' => $row->rice_sourcing_from ?? $from,
                'rice_sourcing_to' => $row->rice_sourcing_to ?? $to,
                'paddy_market_from' => $row->paddy_market_from ?? $from,
                'paddy_market_to' => $row->paddy_market_to ?? $to,
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Schema::table('app_settings', function (Blueprint $table) {
            foreach ([
                'rice_sourcing_from',
                'rice_sourcing_to',
                'rice_sourcing_override_status',
                'rice_sourcing_override_until',
                'paddy_market_from',
                'paddy_market_to',
                'paddy_market_override_status',
                'paddy_market_override_until',
            ] as $col) {
                if (Schema::hasColumn('app_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
