<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('avg_length_maps')) {
            return;
        }

        Schema::table('avg_length_maps', function (Blueprint $table) {
            if (Schema::hasColumn('avg_length_maps', 'wand_id')) {
                $table->dropForeign(['wand_id']);
            }
        });

        Schema::table('avg_length_maps', function (Blueprint $table) {
            $table->dropUnique('avg_length_maps_unique');
        });

        if (Schema::hasColumn('avg_length_maps', 'wand_id')) {
            Schema::table('avg_length_maps', function (Blueprint $table) {
                $table->json('wand_ids')->nullable()->after('form_id');
            });

            foreach (DB::table('avg_length_maps')->get() as $row) {
                if (! empty($row->wand_id)) {
                    DB::table('avg_length_maps')->where('id', $row->id)->update([
                        'wand_ids' => json_encode([(int) $row->wand_id]),
                    ]);
                }
            }

            Schema::table('avg_length_maps', function (Blueprint $table) {
                $table->dropColumn(['wand_id', 'avg_length']);
            });
        }

        Schema::table('avg_length_maps', function (Blueprint $table) {
            $table->unique(['quality_type', 'rice_name_id', 'form_id'], 'avg_length_maps_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('avg_length_maps')) {
            return;
        }

        Schema::table('avg_length_maps', function (Blueprint $table) {
            $table->dropUnique('avg_length_maps_unique');
            $table->unsignedBigInteger('wand_id')->nullable()->after('form_id');
            $table->decimal('avg_length', 10, 2)->nullable();
            $table->dropColumn('wand_ids');
        });

        Schema::table('avg_length_maps', function (Blueprint $table) {
            $table->foreign('wand_id')->references('id')->on('wand')->onDelete('cascade');
            $table->unique(['quality_type', 'rice_name_id', 'form_id', 'wand_id'], 'avg_length_maps_unique');
        });
    }
};
