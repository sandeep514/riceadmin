<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Previously merged analyser into Market Analyst. That change was reverted.
 * Kept as a no-op so already-migrated environments stay consistent.
 * See 2026_10_30_000004_restore_analyser_role_from_market_analyst.
 */
class MergeAnalyserRoleIntoMarketAnalyst extends Migration
{
    public function up()
    {
        // no-op — analyser role must remain
    }

    public function down()
    {
        // no-op
    }
}
