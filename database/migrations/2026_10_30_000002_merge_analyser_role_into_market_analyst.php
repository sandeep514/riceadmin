<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeAnalyserRoleIntoMarketAnalyst extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $marketAnalyst = DB::table('roles')
            ->whereRaw('LOWER(role_name) = ?', ['market analyst'])
            ->orderBy('id')
            ->first();

        if (! $marketAnalyst) {
            $id = DB::table('roles')->insertGetId([
                'role_name' => 'Market Analyst',
                'modules' => null,
                'type' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $marketAnalystId = (int) $id;
        } else {
            $marketAnalystId = (int) $marketAnalyst->id;
            // Keep Market Analyst as a normal web role.
            if (Schema::hasColumn('roles', 'type') && strtolower((string) ($marketAnalyst->type ?? '')) !== 'web') {
                DB::table('roles')->where('id', $marketAnalystId)->update([
                    'type' => 'web',
                    'updated_at' => now(),
                ]);
            }
        }

        $analyserRoles = DB::table('roles')
            ->where(function ($q) {
                $q->whereRaw('LOWER(role_name) = ?', ['analyser'])
                    ->orWhere('type', 'analyser');
            })
            ->where('id', '<>', $marketAnalystId)
            ->get();

        foreach ($analyserRoles as $analyserRole) {
            $fromId = (int) $analyserRole->id;

            if (Schema::hasTable('users')) {
                DB::table('users')->where('role', $fromId)->update(['role' => $marketAnalystId]);
            }
            if (Schema::hasTable('web_plan') && Schema::hasColumn('web_plan', 'role_id')) {
                DB::table('web_plan')->where('role_id', $fromId)->update(['role_id' => $marketAnalystId]);
            }
            if (Schema::hasTable('category_role_map')) {
                // Avoid unique conflicts: move only maps whose category is not already on Market Analyst.
                $existingCategoryIds = DB::table('category_role_map')
                    ->where('role', $marketAnalystId)
                    ->pluck('category')
                    ->all();

                $maps = DB::table('category_role_map')->where('role', $fromId)->get();
                foreach ($maps as $map) {
                    if (in_array($map->category, $existingCategoryIds, false)) {
                        DB::table('category_role_map')->where('id', $map->id)->delete();
                    } else {
                        DB::table('category_role_map')->where('id', $map->id)->update(['role' => $marketAnalystId]);
                    }
                }
            }
            if (Schema::hasTable('web_access') && Schema::hasColumn('web_access', 'role_id')) {
                DB::table('web_access')->where('role_id', $fromId)->update(['role_id' => $marketAnalystId]);
            }

            DB::table('roles')->where('id', $fromId)->delete();
        }
    }

    public function down()
    {
        // Irreversible merge — recreate Analyser only if missing.
        $exists = DB::table('roles')->whereRaw('LOWER(role_name) = ?', ['analyser'])->exists();
        if (! $exists) {
            DB::table('roles')->insert([
                'role_name' => 'analyser',
                'modules' => null,
                'type' => 'analyser',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
