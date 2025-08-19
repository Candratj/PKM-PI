<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length untuk MySQL
        Schema::defaultStringLength(191);

        // Disable timestamp precision untuk kompatibilitas
        if (config('database.default') === 'mysql') {
            Schema::defaultStringLength(191);
        }
    }
}
