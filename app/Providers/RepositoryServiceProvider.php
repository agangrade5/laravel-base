<?php

namespace App\Providers;

use App\Repositories\Contracts\{
    ActivityLogRepositoryInterface,
    UserRepositoryInterface,
    SettingRepositoryInterface,
    DashboardRepositoryInterface,
};

use App\Repositories\{
    ActivityLogRepository,
    UserRepository,
    SettingRepository,
    DashboardRepository,
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

        /* Setting Repository Binding */
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
