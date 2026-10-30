<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Undo the Market Analyst merge: recreate analyser role and move
 * analyser-account users / related plans / category maps back.
 */
class RestoreAnalyserRoleFromMarketAnalyst extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $analyser = DB::table('roles')
            ->where(function ($q) {
                $q->whereRaw('LOWER(role_name) = ?', ['analyser'])
                    ->orWhere('type', 'analyser');
            })
            ->orderBy('id')
            ->first();

        if (! $analyser) {
            $analyserId = (int) DB::table('roles')->insertGetId([
                'role_name' => 'analyser',
                'modules' => null,
                'type' => 'analyser',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $analyserId = (int) $analyser->id;
            if (Schema::hasColumn('roles', 'type') && strtolower((string) ($analyser->type ?? '')) !== 'analyser') {
                DB::table('roles')->where('id', $analyserId)->update([
                    'type' => 'analyser',
                    'updated_at' => now(),
                ]);
            }
            if (strtolower((string) ($analyser->role_name ?? '')) !== 'analyser') {
                DB::table('roles')->where('id', $analyserId)->update([
                    'role_name' => 'analyser',
                    'updated_at' => now(),
                ]);
            }
        }

        $marketAnalyst = DB::table('roles')
            ->whereRaw('LOWER(role_name) = ?', ['market analyst'])
            ->orderBy('id')
            ->first();

        if (! $marketAnalyst) {
            return;
        }

        $marketAnalystId = (int) $marketAnalyst->id;
        if ($marketAnalystId === $analyserId) {
            return;
        }

        if (Schema::hasTable('analyser_accounts') && Schema::hasTable('users')) {
            $userIds = DB::table('analyser_accounts')->pluck('user_id')->filter()->all();
            if ($userIds !== []) {
                DB::table('users')->whereIn('id', $userIds)->update(['role' => $analyserId]);
            }
        }

        if (Schema::hasTable('web_plan') && Schema::hasColumn('web_plan', 'role_id')) {
            $planIds = [];
            if (Schema::hasTable('analyser_accounts') && Schema::hasColumn('analyser_accounts', 'plan_id')) {
                $planIds = DB::table('analyser_accounts')
                    ->whereNotNull('plan_id')
                    ->pluck('plan_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
            }

            if ($planIds !== []) {
                DB::table('web_plan')->whereIn('id', $planIds)->update(['role_id' => $analyserId]);
            }

            // Plans created under analyser or the mistaken Market Analyst rename.
            DB::table('web_plan')
                ->where('role_id', $marketAnalystId)
                ->where(function ($q) {
                    $q->where('title', 'like', 'analyser_%')
                        ->orWhere('title', 'like', 'market_analyst_%')
                        ->orWhereRaw('LOWER(title) like ?', ['analyser %'])
                        ->orWhereRaw('LOWER(title) like ?', ['market analyst %']);
                })
                ->update(['role_id' => $analyserId]);
        }

        if (Schema::hasTable('category_role_map') && Schema::hasTable('web_plan')) {
            $categoryIds = DB::table('web_plan')
                ->where('role_id', $analyserId)
                ->whereNotNull('category_id')
                ->pluck('category_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $existing = DB::table('category_role_map')
                ->where('role', $analyserId)
                ->pluck('category')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($categoryIds as $categoryId) {
                if (in_array($categoryId, $existing, true)) {
                    continue;
                }

                $source = DB::table('category_role_map')
                    ->where('role', $marketAnalystId)
                    ->where('category', $categoryId)
                    ->first();

                $payload = [
                    'role' => $analyserId,
                    'category' => $categoryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('category_role_map', 'status')) {
                    $payload['status'] = $source->status ?? 1;
                }

                DB::table('category_role_map')->insert($payload);
                $existing[] = $categoryId;
            }
        }
    }

    public function down()
    {
        // Intentionally empty — do not re-merge analyser into Market Analyst.
    }
}
