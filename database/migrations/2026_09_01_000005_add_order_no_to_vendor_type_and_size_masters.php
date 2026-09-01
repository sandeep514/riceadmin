<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderNoToVendorTypeAndSizeMasters extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'vendor_packing_types',
        'cartoon_types',
        'cylinder_types',
        'bag_sizes',
        'carton_sizes',
        'cylinder_sizes',
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'order_no')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedInteger('order_no')->nullable()->after('status');
                $blueprint->index('order_no');
            });

            $rows = DB::table($table)->orderBy('id')->get(['id']);
            $order = 1;
            foreach ($rows as $row) {
                DB::table($table)->where('id', $row->id)->update(['order_no' => $order++]);
            }
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'order_no')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('order_no');
            });
        }
    }
}
