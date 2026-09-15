<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVendorCurrenciesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_currencies')) {
            Schema::create('vendor_currencies', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        $now = now();
        foreach (['INR', 'USD'] as $name) {
            if (! DB::table('vendor_currencies')->where('name', $name)->exists()) {
                DB::table('vendor_currencies')->insert([
                    'name' => $name,
                    'description' => null,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('vendor_currencies');
    }
}
