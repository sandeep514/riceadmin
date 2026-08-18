<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChangeVendorSpecificationsSpecForToId extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_specifications') || Schema::hasColumn('vendor_specifications', 'spec_for_id')) {
            return;
        }

        Schema::table('vendor_specifications', function (Blueprint $table) {
            $table->unsignedBigInteger('spec_for_id')->nullable()->after('description');
        });

        if (Schema::hasColumn('vendor_specifications', 'spec_for')) {
            $specFors = DB::table('vendor_spec_fors')->get();
            $byName = [];
            $bySlug = [];
            foreach ($specFors as $row) {
                $byName[mb_strtolower($row->name)] = $row->id;
                $bySlug[Str::slug($row->name)] = $row->id;
            }

            foreach (DB::table('vendor_specifications')->get() as $spec) {
                $raw = trim((string) $spec->spec_for);
                if ($raw === '') {
                    continue;
                }

                $key = mb_strtolower($raw);
                $id = $byName[$key] ?? $bySlug[Str::slug($raw)] ?? null;

                if ($id === null) {
                    $id = DB::table('vendor_spec_fors')->insertGetId([
                        'name' => $raw,
                        'description' => null,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $byName[$key] = $id;
                    $bySlug[Str::slug($raw)] = $id;
                }

                DB::table('vendor_specifications')->where('id', $spec->id)->update([
                    'spec_for_id' => $id,
                ]);
            }

            Schema::table('vendor_specifications', function (Blueprint $table) {
                $table->dropUnique('vendor_specifications_spec_for_unique');
                $table->dropIndex(['spec_for']);
                $table->dropColumn('spec_for');
            });
        }

        if (DB::table('vendor_specifications')->whereNull('spec_for_id')->exists()) {
            $fallbackId = DB::table('vendor_spec_fors')->where('name', 'General')->value('id');
            if (! $fallbackId) {
                $fallbackId = DB::table('vendor_spec_fors')->insertGetId([
                    'name' => 'General',
                    'description' => null,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('vendor_specifications')->whereNull('spec_for_id')->update([
                'spec_for_id' => $fallbackId,
            ]);
        }

        DB::statement('ALTER TABLE vendor_specifications MODIFY spec_for_id BIGINT UNSIGNED NOT NULL');

        Schema::table('vendor_specifications', function (Blueprint $table) {
            $table->unique(['specification', 'spec_for_id'], 'vendor_specifications_spec_for_id_unique');
            $table->foreign('spec_for_id')
                ->references('id')
                ->on('vendor_spec_fors');
        });
    }

    public function down()
    {
        if (! Schema::hasTable('vendor_specifications') || ! Schema::hasColumn('vendor_specifications', 'spec_for_id')) {
            return;
        }

        Schema::table('vendor_specifications', function (Blueprint $table) {
            $table->dropForeign(['spec_for_id']);
            $table->dropUnique('vendor_specifications_spec_for_id_unique');
            $table->string('spec_for', 100)->nullable()->after('description');
        });

        $names = DB::table('vendor_spec_fors')->pluck('name', 'id');
        foreach (DB::table('vendor_specifications')->get() as $spec) {
            DB::table('vendor_specifications')->where('id', $spec->id)->update([
                'spec_for' => $names[$spec->spec_for_id] ?? '',
            ]);
        }

        Schema::table('vendor_specifications', function (Blueprint $table) {
            $table->dropColumn('spec_for_id');
        });

        DB::statement("ALTER TABLE vendor_specifications MODIFY spec_for VARCHAR(100) NOT NULL");

        Schema::table('vendor_specifications', function (Blueprint $table) {
            $table->unique(['specification', 'spec_for'], 'vendor_specifications_spec_for_unique');
            $table->index('spec_for');
        });
    }
}
