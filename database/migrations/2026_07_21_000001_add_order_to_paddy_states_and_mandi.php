<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderToPaddyStatesAndMandi extends Migration
{
    public function up()
    {
        Schema::table('paddyStates', function (Blueprint $table) {
            $table->unsignedInteger('order_no')->nullable()->after('status');
        });

        Schema::table('paddyMandi', function (Blueprint $table) {
            $table->unsignedInteger('order_no')->nullable()->after('status');
        });

        DB::table('paddyStates')->orderBy('id')->get(['id'])->each(function ($row, $index) {
            DB::table('paddyStates')->where('id', $row->id)->update(['order_no' => $index + 1]);
        });

        DB::table('paddyMandi')->orderBy('id')->get(['id'])->each(function ($row, $index) {
            DB::table('paddyMandi')->where('id', $row->id)->update(['order_no' => $index + 1]);
        });

        Schema::table('paddyStates', function (Blueprint $table) {
            $table->unique('order_no', 'paddy_states_order_no_unique');
        });

        Schema::table('paddyMandi', function (Blueprint $table) {
            $table->unique('order_no', 'paddy_mandi_order_no_unique');
        });
    }

    public function down()
    {
        Schema::table('paddyStates', function (Blueprint $table) {
            $table->dropUnique('paddy_states_order_no_unique');
            $table->dropColumn('order_no');
        });

        Schema::table('paddyMandi', function (Blueprint $table) {
            $table->dropUnique('paddy_mandi_order_no_unique');
            $table->dropColumn('order_no');
        });
    }
}
