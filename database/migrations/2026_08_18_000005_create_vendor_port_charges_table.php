<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorPortChargesTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_port_charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('charge');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_port_charges');
    }
}
