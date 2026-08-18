<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorSpecificationsTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_specifications', function (Blueprint $table) {
            $table->id();
            $table->string('specification', 255);
            $table->text('description')->nullable();
            $table->string('spec_for', 100);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['specification', 'spec_for'], 'vendor_specifications_spec_for_unique');
            $table->index('spec_for');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_specifications');
    }
}
