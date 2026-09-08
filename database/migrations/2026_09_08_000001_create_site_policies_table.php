<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitePoliciesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('site_policies')) {
            return;
        }

        Schema::create('site_policies', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title', 255);
            $table->longText('content')->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('order_no')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_policies');
    }
}
