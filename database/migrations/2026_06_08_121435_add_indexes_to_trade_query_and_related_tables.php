<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToTradeQueryAndRelatedTables extends Migration
{
    public function up()
    {
        // Main trade table — heavily filtered in get/all/trades/count and get/trades/filter/{userId}
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            // Critical for the bulk UPDATE that runs on every API call:
            // UPDATE ... WHERE status IN (1,6,4,5,11,12) AND validDays <= '...'
            $table->index(['status', 'validDays'], 'tqm_status_validDays_idx');

            // Core filter for count & list endpoints: WHERE status = 1 AND tradeType = X
            $table->index(['status', 'tradeType'], 'tqm_status_tradeType_idx');

            // Optional filter columns used in both web & app filter endpoints
            $table->index('farmingType', 'tqm_farmingType_idx');
            $table->index('quality_type', 'tqm_quality_type_idx');
            $table->index('quality', 'tqm_quality_idx');
            $table->index('qualityFormLinkWithLivePrice', 'tqm_qualityFormLinkWithLivePrice_idx');
            $table->index('qualityForm', 'tqm_qualityForm_idx');
            $table->index('riceSize', 'tqm_riceSize_idx');

            // Web filter endpoint extras
            $table->index('stateLinkWithLivePrice', 'tqm_stateLinkWithLivePrice_idx');
            $table->index('packingStreamType', 'tqm_packingStreamType_idx');
        });

        // trade_like — eager-loaded / counted per trade in filter endpoints
        Schema::table('trade_like', function (Blueprint $table) {
            $table->index('tradeId', 'tl_tradeId_idx');
            $table->index('userId', 'tl_userId_idx');
        });

        // trade_intrested — eager-loaded per trade in filter endpoints
        Schema::table('trade_intrested', function (Blueprint $table) {
            $table->index('tradeId', 'ti_tradeId_idx');
            $table->index('userId', 'ti_userId_idx');
        });
    }

    public function down()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            $table->dropIndex('tqm_status_validDays_idx');
            $table->dropIndex('tqm_status_tradeType_idx');
            $table->dropIndex('tqm_farmingType_idx');
            $table->dropIndex('tqm_quality_type_idx');
            $table->dropIndex('tqm_quality_idx');
            $table->dropIndex('tqm_qualityFormLinkWithLivePrice_idx');
            $table->dropIndex('tqm_qualityForm_idx');
            $table->dropIndex('tqm_riceSize_idx');
            $table->dropIndex('tqm_stateLinkWithLivePrice_idx');
            $table->dropIndex('tqm_packingStreamType_idx');
        });

        Schema::table('trade_like', function (Blueprint $table) {
            $table->dropIndex('tl_tradeId_idx');
            $table->dropIndex('tl_userId_idx');
        });

        Schema::table('trade_intrested', function (Blueprint $table) {
            $table->dropIndex('ti_tradeId_idx');
            $table->dropIndex('ti_userId_idx');
        });
    }
}
