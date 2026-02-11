<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebBrandsTable extends Migration
{
    public function up()
    {
        Schema::create('web_brands', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('name', 255);
            $table->string('quality', 255)->nullable();
            $table->year('brand_year')->nullable();
            $table->longText('address')->nullable();
            $table->longText('product_mode')->nullable();
            $table->longText('logo')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_brands');
    }
}

