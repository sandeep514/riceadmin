<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBagVendorTable extends Migration
{
    public function up()
    {
        Schema::create('bag_vendor', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name', 256)->nullable();
            $table->string('email', 256)->nullable();
            $table->string('vendor_address', 256)->nullable();
            $table->string('contact_person', 256)->nullable();
            $table->string('contact_number', 256)->nullable();
            $table->string('specialised', 256)->nullable();
            $table->integer('vendor_type');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bag_vendor');
    }
}

