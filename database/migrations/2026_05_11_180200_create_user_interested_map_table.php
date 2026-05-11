<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInterestedMapTable extends Migration
{
    public function up()
    {
        Schema::create('user_interested_map_table', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rice_name_id');
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('grade')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->index('user_id');
            $table->index('rice_name_id');
            $table->index('form_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_interested_map_table');
    }
}
