<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandAttachmentMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('brand_attachment_milestone3', function (Blueprint $table) {
            $table->id();
            $table->integer('brand_id');
            $table->text('attachment');
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('brand_attachment_milestone3');
    }
}

