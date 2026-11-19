<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanPageVisitsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('plan_page_visits')) {
            return;
        }

        Schema::create('plan_page_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('platform', 20)->nullable(); // web | mobile | app
            $table->unsignedInteger('role_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['user_id', 'visited_at'], 'plan_page_visits_user_visited_idx');
            $table->index('visited_at', 'plan_page_visits_visited_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_page_visits');
    }
}
