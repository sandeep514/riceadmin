<?php

namespace App\Providers;

use App\DatabaseBackupLog;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // For subfolder deployments (e.g. /staging/public), set APP_URL to the full base
        // so redirects like login -> dashboard generate the correct path.
        // Do not force a bare host APP_URL — that breaks local Herd domains (e.g. riceadmin.test).
        $appUrl = config('app.url');
        if (is_string($appUrl) && $appUrl !== '') {
            $configuredPath = rtrim((string) (parse_url($appUrl, PHP_URL_PATH) ?? ''), '/');
            if ($configuredPath !== '' && $configuredPath !== '/') {
                URL::forceRootUrl($appUrl);
            }
        }
        Schema::defaultStringLength(191);

        View::composer('components.header', function ($view) {
            $last = null;
            $overdue = false;
            $daysAgo = null;

            try {
                $last = DatabaseBackupLog::lastDownload();
                $overdue = DatabaseBackupLog::isOverdue($last);
                $daysAgo = DatabaseBackupLog::daysSinceLastDownload($last);
            } catch (\Throwable $e) {
                $last = null;
                $overdue = false;
                $daysAgo = null;
            }

            $view->with([
                'backupOverdue' => $overdue,
                'lastBackupAt' => $last?->downloaded_at,
                'lastBackupDaysAgo' => $daysAgo,
            ]);
        });
    }
}
