<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNewsDateToWebNewsRunnerTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('web_news_runner', 'news_date')) {
            Schema::table('web_news_runner', function (Blueprint $table) {
                $table->date('news_date')->nullable()->after('newsType');
            });
        }

        DB::statement('UPDATE web_news_runner SET news_date = DATE(created_at) WHERE news_date IS NULL');
    }

    public function down()
    {
        if (Schema::hasColumn('web_news_runner', 'news_date')) {
            Schema::table('web_news_runner', function (Blueprint $table) {
                $table->dropColumn('news_date');
            });
        }
    }
}
