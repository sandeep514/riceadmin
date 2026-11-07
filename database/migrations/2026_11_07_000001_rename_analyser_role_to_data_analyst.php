<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename role display name analyser → Data Analyst.
 * Keep type=analyser as the stable technical identifier.
 */
class RenameAnalyserRoleToDataAnalyst extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $aliases = ['analyser', 'analyzer', 'market analyst', 'data analyst'];

        $role = DB::table('roles')
            ->where(function ($q) use ($aliases) {
                foreach ($aliases as $alias) {
                    $q->orWhereRaw('LOWER(role_name) = ?', [$alias]);
                }
                if (Schema::hasColumn('roles', 'type')) {
                    $q->orWhere('type', 'analyser');
                }
            })
            ->orderBy('id')
            ->first();

        if (! $role) {
            $payload = [
                'role_name' => 'Data Analyst',
            ];
            if (Schema::hasColumn('roles', 'type')) {
                $payload['type'] = 'analyser';
            }
            if (Schema::hasColumn('roles', 'created_at')) {
                $payload['created_at'] = now();
                $payload['updated_at'] = now();
            }
            DB::table('roles')->insert($payload);

            return;
        }

        $update = [
            'role_name' => 'Data Analyst',
        ];
        if (Schema::hasColumn('roles', 'type') && strtolower((string) ($role->type ?? '')) !== 'analyser') {
            $update['type'] = 'analyser';
        }
        if (Schema::hasColumn('roles', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('roles')->where('id', $role->id)->update($update);
    }

    public function down()
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')
            ->whereRaw('LOWER(role_name) = ?', ['data analyst'])
            ->update([
                'role_name' => 'analyser',
            ]);
    }
}
