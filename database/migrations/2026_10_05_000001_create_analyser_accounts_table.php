<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAnalyserAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('analyser_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('received_amount', 12, 2)->nullable();
            $table->string('contact_person_name', 255)->nullable();
            $table->string('contact_mobile', 191)->nullable();
            $table->boolean('has_historical_access')->default(0);
            $table->integer('download_limit')->nullable();
            $table->integer('downloads_used')->default(0);
            $table->boolean('has_today_access')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id');
        });

        // Ensure the analyser role exists (historical price viewer/downloader).
        $exists = DB::table('roles')->where('role_name', 'analyser')->exists();
        if (! $exists) {
            DB::table('roles')->insert([
                'role_name' => 'analyser',
                'modules' => null,
                'type' => 'analyser',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('analyser_accounts');
    }
}
