<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('contract_no', 191);
            $table->string('truck_no', 191);
            $table->string('driver_no', 191);
            $table->text('contract_copy');
            $table->text('bill_copy');
            $table->text('bilty_copy');
            $table->text('kanta_parchi');
            $table->string('due_days', 191);
            $table->date('due_date');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
}

