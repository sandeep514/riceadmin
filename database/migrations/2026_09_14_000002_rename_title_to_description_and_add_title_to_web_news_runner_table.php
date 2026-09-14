<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTitleToDescriptionAndAddTitleToWebNewsRunnerTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('web_news_runner', 'title') && ! Schema::hasColumn('web_news_runner', 'description')) {
            Schema::table('web_news_runner', function (Blueprint $table) {
                $table->renameColumn('title', 'description');
            });
        }

        if (! Schema::hasColumn('web_news_runner', 'title')) {
            Schema::table('web_news_runner', function (Blueprint $table) {
                $table->string('title')->nullable()->after('id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('web_news_runner', 'title') && Schema::hasColumn('web_news_runner', 'description')) {
            Schema::table('web_news_runner', function (Blueprint $table) {
                $table->dropColumn('title');
            });

            Schema::table('web_news_runner', function (Blueprint $table) {
                $table->renameColumn('description', 'title');
            });
        }
    }
}
