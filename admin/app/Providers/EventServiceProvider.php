<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use App\Listeners\LogAuthenticationEvent;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class EventServiceProvider extends ServiceProvider
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
        Vite::prefetch(concurrency: 3);
        User::observe(UserObserver::class);
    }

    protected $listen = [
        Login::class => [
            LogAuthenticationEvent::class,
        ],
        Logout::class => [
            LogAuthenticationEvent::class,
        ],
    ];
}
