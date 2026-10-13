<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddApiTokenIndexToUsersTable extends Migration
{
    public function up()
    {
        // users.api_token is TEXT and unindexed: every token lookup
        // (e.g. POST /api/portal/session) does a full table scan.
        // Tokens are sha256 hex (64 chars), so a 191-char prefix index is safe.
        $this->addPrefixedIndexIfMissing(
            'users',
            'idx_users_api_token',
            '`api_token`(191)'
        );

        // Portal session hydrates these per-user rows on every call;
        // none of them has a user_id index.
        $this->addPrefixedIndexIfMissing('web_user_subscription', 'idx_web_user_subscription_user_id', '`user_id`');
        $this->addPrefixedIndexIfMissing('web_personal_details', 'idx_web_personal_details_user_id', '`user_id`');
        $this->addPrefixedIndexIfMissing('web_business_details', 'idx_web_business_details_user_id', '`user_id`');
        $this->addPrefixedIndexIfMissing('web_user_attachment', 'idx_web_user_attachment_user_id', '`user_id`');
    }

    public function down()
    {
        $this->dropIndexIfExists('users', 'idx_users_api_token');
        $this->dropIndexIfExists('web_user_subscription', 'idx_web_user_subscription_user_id');
        $this->dropIndexIfExists('web_personal_details', 'idx_web_personal_details_user_id');
        $this->dropIndexIfExists('web_business_details', 'idx_web_business_details_user_id');
        $this->dropIndexIfExists('web_user_attachment', 'idx_web_user_attachment_user_id');
    }

    private function addPrefixedIndexIfMissing(string $table, string $index, string $columnsSql): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD INDEX `%s` (%s)',
            $table,
            $index,
            $columnsSql
        ));
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }

        DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $index));
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
             LIMIT 1',
            [$table, $index]
        );

        return $rows !== [];
    }
}
