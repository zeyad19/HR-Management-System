<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Employee;
use App\Models\GeneralSetting;
use App\Observers\EmployeeObserver;
use App\Observers\GeneralSettingObserver;
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
          Employee::observe(EmployeeObserver::class);
    GeneralSetting::observe(GeneralSettingObserver::class);
        //
    }
}
