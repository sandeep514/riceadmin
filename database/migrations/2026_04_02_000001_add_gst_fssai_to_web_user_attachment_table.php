<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_user_attachment', function (Blueprint $table) {
            $table->string('gst_fssai', 512)->nullable()->after('panCard');
        });

        if (Schema::hasColumn('web_user_attachment', 'gst_fssai')) {
            DB::table('web_user_attachment')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    if (! empty($row->gst_fssai)) {
                        continue;
                    }
                    $path = null;
                    if (! empty($row->gstCard)) {
                        $path = 'gst/' . ltrim($row->gstCard, '/');
                    } elseif (! empty($row->fssaiCard)) {
                        $path = 'fssai/' . ltrim($row->fssaiCard, '/');
                    }
                    if ($path !== null) {
                        DB::table('web_user_attachment')->where('id', $row->id)->update(['gst_fssai' => $path]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('web_user_attachment', function (Blueprint $table) {
            $table->dropColumn('gst_fssai');
        });
    }
};
