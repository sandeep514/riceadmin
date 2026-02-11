<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMillStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('mill_statuses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('seller');
            $table->boolean('visit_status')->default(0);
            $table->text('remarks');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('seller');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mill_statuses');
    }
}

