<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderNoToTestimonialVideoTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('testimonial_video') || Schema::hasColumn('testimonial_video', 'order_no')) {
            return;
        }

        Schema::table('testimonial_video', function (Blueprint $table) {
            $table->unsignedInteger('order_no')->nullable()->after('file');
            $table->index('order_no');
        });

        $rows = DB::table('testimonial_video')->orderBy('id')->get(['id']);
        $order = 1;
        foreach ($rows as $row) {
            DB::table('testimonial_video')->where('id', $row->id)->update(['order_no' => $order++]);
        }
    }

    public function down()
    {
        if (! Schema::hasTable('testimonial_video') || ! Schema::hasColumn('testimonial_video', 'order_no')) {
            return;
        }

        Schema::table('testimonial_video', function (Blueprint $table) {
            $table->dropColumn('order_no');
        });
    }
}
