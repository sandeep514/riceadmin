<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTable extends Migration
{
    public function up()
    {
        Schema::create('coupon', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_name', 256);
            $table->string('coupon_feature', 256);
            $table->text('coupon_description');
            $table->string('coupon_percentage', 256);
            $table->string('coupon_expiry', 256);
            $table->string('maxDiscount', 256);
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon');
    }
}

