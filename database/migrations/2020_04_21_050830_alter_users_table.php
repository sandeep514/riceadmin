<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table){
            $table->text('address')->after('password')->nullable();
            $table->string('phone')->after('address')->nullable();
            $table->string('mobile')->after('phone');
            $table->string('gst_no')->after('mobile')->nullable();
            $table->string('state')->after('gst_no')->nullable();
            $table->string('city')->after('gst_no')->nullable();
            $table->unsignedBigInteger('role')->index()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table){
            $table->drop('address');
            $table->drop('phone');
            $table->drop('mobile');
            $table->drop('gst_no');
            $table->drop('state');
            $table->drop('city');
            $table->drop('role');
        });
    }
}
