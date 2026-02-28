<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTermDiscountsToWebPlanTable extends Migration
{
    public function up()
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->decimal('monthly_discount_percentage', 5, 2)->nullable()->after('yearly_final_amount');
            $table->decimal('quarterly_discount_percentage', 5, 2)->nullable()->after('monthly_discount_percentage');
            $table->decimal('yearly_discount_percentage', 5, 2)->nullable()->after('quarterly_discount_percentage');
        });
    }

    public function down()
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_discount_percentage',
                'quarterly_discount_percentage',
                'yearly_discount_percentage'
            ]);
        });
    }
}

