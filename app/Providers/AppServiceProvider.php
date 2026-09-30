<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Http;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\Log;

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
        | Global SSL certificate
        |--------------------------------------------------------------------------
        |
        | Apply a global SSL certificate to all HTTP requests.
        |
        */
        $caBundle = storage_path('certs/cacert.pem');

        if (is_file($caBundle)) {
            Http::globalOptions([
                'verify' => $caBundle,
            ]);
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
