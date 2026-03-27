<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fix #4: Warn loudly if APP_KEY is not set — avoids silent encryption failures
        // and blank-page mystery errors on fresh deployments.
        if (empty(config('app.key'))) {
            Log::critical('APP_KEY is not set. Run: php artisan key:generate');

            if (app()->isLocal()) {
                throw new \RuntimeException(
                    'APP_KEY is missing. Run [php artisan key:generate] before starting the app.'
                );
            }
        }
    }
}
