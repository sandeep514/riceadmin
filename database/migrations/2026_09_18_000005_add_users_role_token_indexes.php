<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUsersRoleTokenIndexes extends Migration
{
    public function up()
    {
        $this->addPrefixedIndexIfMissing(
            'users',
            'idx_users_role_user_token',
            '`role`, `user_token`(191)'
        );
        $this->addPrefixedIndexIfMissing(
            'users',
            'idx_users_usd_role_user_token',
            '`usd_role`, `user_token`(191)'
        );
    }

    public function down()
    {
        $this->dropIndexIfExists('users', 'idx_users_role_user_token');
        $this->dropIndexIfExists('users', 'idx_users_usd_role_user_token');
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
