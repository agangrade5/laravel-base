<?php

namespace App\Providers;

use App\Repositories\Contracts\{
    UserRepositoryInterface,
    UserLocationRepositoryInterface,
    SettingRepositoryInterface,
    DashboardRepositoryInterface,
    ActivityLogRepositoryInterface,
};

use App\Repositories\{
    UserRepository,
    UserLocationRepository,
    SettingRepository,
    DashboardRepository,
    ActivityLogRepository,
};

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     *
     */
    public function register(): void
    {
        // User Repository Binding
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        // User Location Repository Binding
        $this->app->bind(
            UserLocationRepositoryInterface::class,
            UserLocationRepository::class
        );
        // Setting Repository Binding
        $this->app->bind(
            SettingRepositoryInterface::class,
            SettingRepository::class
        );
        /* Dashboard Repository Binding */
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
        // Activity Repository Binding
        $this->app->bind(
            ActivityLogRepositoryInterface::class,
            ActivityLogRepository::class
        );

    }

    public function boot(): void
    {
        //
    }
}
