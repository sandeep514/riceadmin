<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiPricesToWebPlanTable extends Migration
{
    public function up()
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->decimal('monthly_price', 10, 2)->nullable()->after('discount_percentage');
            $table->decimal('quarterly_price', 10, 2)->nullable()->after('monthly_price');
            $table->decimal('yearly_price', 10, 2)->nullable()->after('quarterly_price');
            $table->decimal('monthly_final_amount', 10, 2)->nullable()->after('yearly_price');
            $table->decimal('quarterly_final_amount', 10, 2)->nullable()->after('monthly_final_amount');
            $table->decimal('yearly_final_amount', 10, 2)->nullable()->after('quarterly_final_amount');
        });
    }

    public function down()
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_price',
                'quarterly_price',
                'yearly_price',
                'monthly_final_amount',
                'quarterly_final_amount',
                'yearly_final_amount'
            ]);
        });
    }
}

