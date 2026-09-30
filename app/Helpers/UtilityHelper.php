<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class UtilityHelper
{
    /**
     * Return script with nonce
     *
     * @param string $path
     *
     * @return string
     */
    public static function returnScriptWithNonce(string $path): string
    {
        return '<script nonce="' . csp_nonce('script') . '" src="' . $path . '"></script>';
    }

    /**
     * Create custom activity log.
     *
     * @param string $logName
     * @param string $description
     * @param ?Model $subject
     * @param ?array $properties
     *
     * @return Activity
     */
    public static function customActivityLog(
        string $logName,
        string $description,
        ?Model $subject = null,
        ?array $properties = null
    ): Activity {
        $activity = activity($logName);

        if ($subject) {
            $activity->performedOn($subject);
        }

        /*
        |--------------------------------------------------------------------------
        | Causer
        |--------------------------------------------------------------------------
        | Causer is the user who is performing the action or causing the event.
        | It can be null if the event is not caused by a user.
        */
        $causer = auth()->user() ?? $subject;

        if ($causer) {
            $activity->causedBy($causer);
        }

        if (!empty($properties)) {
            $activity->withProperties($properties);
        }

        return $activity->log($description);
    }

    /**
     * Format date time.
     *
     * @param mixed $dateTime
     * @param ?string $format
     *
     * @return string
     */
    public static function formatDateTime(
        mixed $dateTime,
        ?string $format = null
    ): string {
        if (empty($dateTime)) {
            return '';
        }

        $format ??= config('constants.date_format.admin_display');

        $timezone = auth()->user()?->timezone
            ?? session('user_timezone')
            ?? config('app.timezone', 'UTC');

        return Carbon::parse($dateTime)
            ->timezone($timezone)
            ->format($format);
    }

    /**
     * Generate OTP
     *
     * @param array $otpDetails
     *
     * @return string
     */
    public static function generateOtp(array $otpDetails): string
    {
        $length = (int) ($otpDetails['otp_length'] ?? 6);

        if (!empty($otpDetails['is_default'])) {
            return str_pad(
                (string) ($otpDetails['default'] ?? '999999'),
                $length,
                '0',
                STR_PAD_LEFT
            );
        }

        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return (string) random_int($min, $max);
    }

    /**
     * Set user timezone.
     *
     * @param mixed $user
     * @param ?string $timezone
     *
     * @return void
     */
    public static function setUserTimezone(
        $user,
        ?string $timezone = null
    ): void {
        if (!$timezone) {
            return;
        }

        $timezoneAliases = [
            'Asia/Calcutta' => 'Asia/Kolkata',
        ];

        $timezone = $timezoneAliases[$timezone] ?? $timezone;

        try {
            new \DateTimeZone($timezone);

            $user->timezone = $timezone;
            $user->save();

            session([
                'user_timezone' => $timezone,
            ]);
        } catch (\Exception $e) {
            // Invalid timezone - keep existing timezone
            session([
                'user_timezone' => $user->timezone
                    ?? config('app.timezone', 'UTC'),
            ]);
        }
    }
}
