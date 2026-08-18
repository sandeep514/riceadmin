<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVendorSpecForsTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_spec_fors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('name');
            $table->index('status');
        });

        $now = now();
        $rows = [];
        foreach (['Vendor', 'Product', 'Quality', 'Packing', 'Bag', 'Service'] as $name) {
            $rows[] = [
                'name' => $name,
                'description' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('vendor_spec_fors')->insert($rows);
    }

    public function down()
    {
        Schema::dropIfExists('vendor_spec_fors');
    }
}
