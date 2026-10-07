<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\MailConfigService;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | File Upload Service
        |--------------------------------------------------------------------------
        |
        | Registered as a singleton so the S3 disk (built from the settings table)
        | is created only once per request. It is lazy, meaning the service is
        | only instantiated on first use, so no database query runs at boot time.
        |
        */
        $this->app->singleton(FileUploadService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(
        MailConfigService $mailConfigService,
        SettingRepositoryInterface $settingRepository
    ): void
    {
        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | Apply mail configuration from settings
        |--------------------------------------------------------------------------
        |
        | Do not query the settings table while running Artisan commands.
        | During commands like migrate:fresh, migrations may not have
        | created the settings table yet.
        |
        */

        if (! app()->runningInConsole()) {
            $mailConfigService->apply();
        }

        /*
        |--------------------------------------------------------------------------
        | Password reset expiry
        |--------------------------------------------------------------------------
        |
        | Apply the password reset expiry from the settings table.
        |
        */
        try {
            $general = $settingRepository->getSettingArray('general', config('constants.settings.general', []));

            if (!empty($general['password_reset_expiry'])) {
                config(['auth.passwords.users.expire' => (int) $general['password_reset_expiry']]);
            }
        } catch (\Throwable $e) {
            // Fail silently if settings table isn't migrated yet (e.g. during fresh install)
            Log::channel('auth')->error('Failed to apply password reset expiry: ' . $e->getMessage());
        }
    }
}
