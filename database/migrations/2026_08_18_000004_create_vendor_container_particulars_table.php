<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorContainerParticularsTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_container_particulars', function (Blueprint $table) {
            $table->id();
            $table->string('particular', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('particular');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_container_particulars');
    }
}
