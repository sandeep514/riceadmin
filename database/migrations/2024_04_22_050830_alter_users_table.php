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
        if (!Schema::hasColumn('users', 'address')) {
            Schema::table('users', function(Blueprint $table){
                $table->text('address')->after('password')->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function(Blueprint $table){
                $table->string('phone')->after('address')->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function(Blueprint $table){
                $table->string('mobile')->after('phone');
            });
        }
        if (!Schema::hasColumn('users', 'gst_no')) {
            Schema::table('users', function(Blueprint $table){
                $table->string('gst_no')->after('mobile')->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'state')) {
            Schema::table('users', function(Blueprint $table){
                $table->string('state')->after('gst_no')->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'city')) {
            Schema::table('users', function(Blueprint $table){
                $table->string('city')->after('gst_no')->nullable();
            });
        }
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function(Blueprint $table){
                $table->unsignedBigInteger('role')->index()->after('city');
            });
        }
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
