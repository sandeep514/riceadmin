<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountAndDiscountToWebPlanTable extends Migration
{
    public function up()
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('description');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->dropColumn(['amount', 'discount_percentage']);
        });
    }
}

