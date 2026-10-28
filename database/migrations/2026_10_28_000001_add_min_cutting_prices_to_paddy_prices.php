<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Split cutting prices into min/max.
 * Existing hand_cutting_price / machine_cutting_price are treated as MAX
 * (no data move needed). New min_* columns default to blank placeholder.
 */
class AddMinCuttingPricesToPaddyPrices extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('paddy_prices')) {
            return;
        }

        if (! Schema::hasColumn('paddy_prices', 'min_hand_cutting_price')) {
            Schema::table('paddy_prices', function (Blueprint $table) {
                if (Schema::hasColumn('paddy_prices', 'crop_year')) {
                    $table->string('min_hand_cutting_price', 256)->nullable()->after('crop_year');
                } else {
                    $table->string('min_hand_cutting_price', 256)->nullable();
                }
            });
        }

        if (! Schema::hasColumn('paddy_prices', 'min_machine_cutting_price')) {
            Schema::table('paddy_prices', function (Blueprint $table) {
                if (Schema::hasColumn('paddy_prices', 'min_hand_cutting_price')) {
                    $table->string('min_machine_cutting_price', 256)->nullable()->after('min_hand_cutting_price');
                } else {
                    $table->string('min_machine_cutting_price', 256)->nullable();
                }
            });
        }

        // Existing single prices are max; leave mins empty until admin fills them.
        DB::table('paddy_prices')
            ->where(function ($q) {
                $q->whereNull('min_hand_cutting_price')
                    ->orWhere('min_hand_cutting_price', '');
            })
            ->update(['min_hand_cutting_price' => '----']);

        DB::table('paddy_prices')
            ->where(function ($q) {
                $q->whereNull('min_machine_cutting_price')
                    ->orWhere('min_machine_cutting_price', '');
            })
            ->update(['min_machine_cutting_price' => '----']);
    }

    public function down()
    {
        if (! Schema::hasTable('paddy_prices')) {
            return;
        }

        Schema::table('paddy_prices', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('paddy_prices', 'min_hand_cutting_price')) {
                $cols[] = 'min_hand_cutting_price';
            }
            if (Schema::hasColumn('paddy_prices', 'min_machine_cutting_price')) {
                $cols[] = 'min_machine_cutting_price';
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
}
