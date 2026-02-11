<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->string('companyname', 100)->nullable();
            $table->string('country', 256)->nullable();
            $table->string('zip_code', 256)->nullable();
            $table->string('import_port', 256)->nullable();
            $table->string('contact_person_name', 256)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 191)->nullable();
            $table->string('mobile', 191)->nullable();
            $table->string('gst_no', 191)->nullable();
            $table->unsignedBigInteger('state')->nullable();
            $table->string('city', 191)->nullable();
            $table->unsignedBigInteger('role')->nullable();
            $table->integer('usd_role')->default(0);
            $table->integer('bagCategory')->nullable()->default(0);
            $table->string('remember_token', 100)->nullable();
            $table->string('expired_on', 256)->nullable();
            $table->integer('is_usd_active')->default(0);
            $table->integer('is_INR_active')->default(0);
            $table->integer('is_active_by_admin')->default(0);
            $table->integer('otp')->nullable();
            $table->text('has_validation')->nullable();
            $table->integer('status')->default(1);
            $table->integer('is_viewed_by_admin')->default(0);
            $table->text('api_token')->nullable();
            $table->text('user_token')->nullable();
            $table->string('transaction_id', 256)->nullable();
            $table->text('message')->nullable();
            $table->integer('planId')->nullable()->default(0);
            $table->integer('userType')->default(1);
            $table->string('user_from', 255)->default('app');
            $table->string('stripe_customer_id', 256)->nullable();
            $table->string('stripe_payment_method', 256)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('role');
            $table->index('state');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

