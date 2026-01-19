<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PcDevices;
use App\Observers\PcDeviceObserver;
use App\Models\Announcement;
use App\Observers\AnnouncementObserver;
use App\Observers\DesktopLockAppObserver;

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
        //
        Announcement::observe(AnnouncementObserver::class);
        PcDevices::observe(PcDeviceObserver::class);
        PcDevices::observe(DesktopLockAppObserver::class);
    }
}
