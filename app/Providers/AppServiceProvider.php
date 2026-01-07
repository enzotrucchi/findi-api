<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

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
        // Send hidden copy to admin on every outbound mail when configured
        if (config('mail.admin_address')) {
            Mail::alwaysBcc(config('mail.admin_address'));
        }
    }
}
