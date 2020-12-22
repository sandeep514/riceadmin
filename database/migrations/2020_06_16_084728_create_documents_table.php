<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('contract_no');
            $table->string('truck_no');
            $table->string('driver_no');
            $table->text('contract_copy');
            $table->text('bill_copy');
            $table->text('bilty_copy');
            $table->text('kanta_parchi');
            $table->string('due_days');
            $table->date('due_date');
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
