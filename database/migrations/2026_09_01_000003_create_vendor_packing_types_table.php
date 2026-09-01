<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorPackingTypesTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_packing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('name');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_packing_types');
    }
}
