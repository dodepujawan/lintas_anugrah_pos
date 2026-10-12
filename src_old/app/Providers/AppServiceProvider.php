<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Force HTTPS in non-local environments
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // 🔥 SUPER ADMIN BYPASS
        Gate::before(function ($user, $ability) {
            if ($user->id == 1) {
                return true;
            }
        });
    }
}
