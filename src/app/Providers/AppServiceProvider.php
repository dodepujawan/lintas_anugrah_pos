<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

    public function boot(): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (! $this->app->environment(['production', 'online'])) {
                return;
            }

            if (config('safety.allow_destructive_commands')) {
                return;
            }

            if (! in_array($event->command, config('safety.destructive_commands', []), true)) {
                return;
            }

            throw new \RuntimeException(
                "Command '{$event->command}' diblokir di production agar data tidak terhapus. "
                ."Gunakan backup dan set ALLOW_DESTRUCTIVE_COMMANDS=true hanya jika benar-benar diperlukan."
            );
        });

        View::composer('layouts.app', function ($view) {
            $view->with('sidebarMenus', Menu::sidebarForUser(auth()->user()));
        });

        if ($this->app->environment(['production', 'online'])) {
            URL::forceScheme('https');
        }
    }
}
