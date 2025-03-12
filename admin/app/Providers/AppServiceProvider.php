<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Device;
use App\Models\RegisteredCard;
use App\Models\Semester;
use App\Models\StudentInfo;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\AuditObserver;

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
        User::observe(AuditObserver::class);
        Announcement::observe(AuditObserver::class);
        Device::observe(AuditObserver::class);
        RegisteredCard::observe(AuditObserver::class);
        StudentInfo::observe(AuditObserver::class);
        Semester::observe(AuditObserver::class);
        Vite::prefetch(concurrency: 3);
    }
}
