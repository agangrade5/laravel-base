<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
class DateTimeService
{
    /**
     * Aliases for timezones.
     */
    private const TIMEZONE_ALIASES = [
        'Asia/Calcutta' => 'Asia/Kolkata',
    ];

    /**
     * Cached format, so the settings table is queried once per request.
     */
    private ?string $selectedFormat = null;

    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository
    ) {
    }

    /**
     * Format a date using the admin-selected format (settings table),
     * converted to the current user's timezone.
     *
     * Pass $format to override it (e.g. 'd M Y' for date only).
     */
    public function format(mixed $dateTime, ?string $format = null): string
    {
        if (empty($dateTime)) {
            return '';
        }

        return Carbon::parse($dateTime)
            ->timezone($this->timezone())
            ->format($format ?? $this->selectedFormat());
    }

    /**
     * Current date time in the user's timezone.
     */
    public function now(?string $format = null): string
    {
        return $this->format(Carbon::now(), $format);
    }

    /**
     * Resolve the timezone for the current user.
     */
    public function timezone(): string
    {
        return auth()->user()?->timezone
            ?? session('user_timezone')
            ?? config('app.timezone', 'UTC');
    }

    /**
     * Admin-selected "Date & Time Format" from general settings.
     */
    public function selectedFormat(): string
    {
        if ($this->selectedFormat !== null) {
            return $this->selectedFormat;
        }

        $default = config('constants.settings.general.date_time_format')
            ?? config('constants.date_format.admin_display');

        try {
            $general = $this->settingRepository->getSettingArray(
                'general',
                config('constants.settings.general', [])
            );

            return $this->selectedFormat = $general['date_time_format'] ?? $default;
        } catch (\Throwable $e) {
            // Settings table missing or DB error: fall back to the default
            return $this->selectedFormat = $default;
        }
    }

    /**
     * Save the user's timezone (validated) and keep it in the session.
     */
    public function setUserTimezone($user, ?string $timezone = null): void
    {
        if (!$timezone) {
            return;
        }

        $timezone = self::TIMEZONE_ALIASES[$timezone] ?? $timezone;

        try {
            new \DateTimeZone($timezone);

            $user->timezone = $timezone;
            $user->save();

            session(['user_timezone' => $timezone]);
        } catch (\Exception $e) {
            // Invalid timezone: keep the existing one
            session([
                'user_timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            ]);
        }
    }
}
