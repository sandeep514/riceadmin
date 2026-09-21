<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCurrencyIdToWebForwarderCharges extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_forwarder_charges')) {
            return;
        }

        if (! Schema::hasColumn('web_forwarder_charges', 'currency_id')) {
            Schema::table('web_forwarder_charges', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('currency')->index();
            });
        }

        if (! Schema::hasTable('vendor_currencies')) {
            return;
        }

        $currencies = DB::table('vendor_currencies')->get(['id', 'name']);
        foreach ($currencies as $currency) {
            $name = trim((string) $currency->name);
            if ($name === '') {
                continue;
            }

            DB::table('web_forwarder_charges')
                ->whereNull('currency_id')
                ->where(function ($query) use ($currency, $name) {
                    $query->where('currency', $name)
                        ->orWhereRaw('UPPER(currency) = ?', [strtoupper($name)])
                        ->orWhere('currency', (string) $currency->id);
                })
                ->update([
                    'currency_id' => (int) $currency->id,
                    'currency' => $name,
                ]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('web_forwarder_charges') && Schema::hasColumn('web_forwarder_charges', 'currency_id')) {
            Schema::table('web_forwarder_charges', function (Blueprint $table) {
                $table->dropColumn('currency_id');
            });
        }
    }
}
