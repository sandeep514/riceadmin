<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCropYearToPaddyPrices extends Migration
{
    public function up()
    {
        Schema::table('paddy_prices', function (Blueprint $table) {
            $table->unsignedSmallInteger('crop_year')->nullable()->after('quality_id');
        });

        DB::table('paddy_prices')
            ->orderBy('id')
            ->get(['id', 'created_at'])
            ->each(function ($row) {
                $year = $row->created_at
                    ? Carbon::parse($row->created_at)->year
                    : now()->year;

                DB::table('paddy_prices')
                    ->where('id', $row->id)
                    ->update(['crop_year' => $year]);
            });
    }

    public function down()
    {
        Schema::table('paddy_prices', function (Blueprint $table) {
            $table->dropColumn('crop_year');
        });
    }
}
