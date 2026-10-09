<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Http\Requests\Backend\Setting\{
    OtpSettingRequest,
    TwilioSettingRequest,
    EmailSettingRequest,
    AwsSettingRequest,
    EnableGoogle2faRequest
};
use Illuminate\Support\Facades\{Auth, Crypt, Artisan, Log, Cache};
use Illuminate\Support\Str;
use Illuminate\Http\{RedirectResponse, JsonResponse};
use App\Helpers\UtilityHelper;
use App\Services\GoogleTwoFactorService;

class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param SettingRepositoryInterface $settingRepository
     * @param GoogleTwoFactorService $googleTwoFactorService
     *
     * @return void
     */
    public function __construct(
        protected SettingRepositoryInterface $settingRepository,
        private readonly GoogleTwoFactorService $googleTwoFactorService,
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        $settings = $this->settingRepository->getAllSettingsFormatted($user?->id);

        return view('backend.admin.settings', [
            'title' => 'Settings',
            'user' => $user,
            'settings' => $settings,
            'otpData' => $settings['otp'],
            'twilioData' => $settings['twilio'],
            'mailData' => $settings['mail'],
            'awsData' => $settings['aws'],
            'generalData' => $settings['general'],
        ]);
    }

    /**
     * Update general settings (pagination limit / password reset expiry) via AJAX.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateGeneralSettings(Request $request): JsonResponse
    {
        $paginationOptions = implode(',', array_keys(config('constants.general_options.pagination_limit', [])));
        $expiryOptions = implode(',', array_keys(config('constants.general_options.password_reset_expiry', [])));

        $validated = $request->validate([
            'pagination_limit' => "sometimes|required|integer|in:{$paginationOptions}",
            'password_reset_expiry' => "sometimes|required|integer|in:{$expiryOptions}",
        ]);

        if (empty($validated)) {
            return response()->json([
                'status' => false,
                'message' => 'No valid setting provided.',
            ], 422);
        }

        try {
            $setting = $this->settingRepository->saveSetting(
                'general',
                array_merge(
                    $this->settingRepository->getSettingArray('general', config('constants.settings.general', [])),
                    $validated
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Updated setting successfully.',
                $setting,
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Setting updated successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to update setting.',
            ], 500);
        }
    }

    /**
     * Toggle maintenance mode via AJAX.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function toggleMaintenanceMode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maintenance_mode' => 'required|in:0,1',
        ]);

        try {
            if ($validated['maintenance_mode'] === '1') {
                // Generate a fresh secret every time site goes down
                $secret = Str::random(32);

                Artisan::call('down', [
                    '--secret' => $secret,
                ]);

                $bypassUrl = url('/' . $secret);

                /*
                |--------------------------------------------------------------------------
                | Activity Log
                |--------------------------------------------------------------------------
                */
                UtilityHelper::customActivityLog(
                    'Setting',
                    'Maintenance mode enabled.',
                    null,
                    [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                );

                return response()->json([
                    'status' => true,
                    'message' => 'Maintenance mode enabled.',
                    'bypass_url' => $bypassUrl,
                ]);
            }

            Artisan::call('up');

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Maintenance mode disabled.',
                null,
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Maintenance mode disabled. Site is live now.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to toggle maintenance mode.',
            ], 500);
        }
    }

    /**
     * Clear all application caches.
     *
     * @return JsonResponse
     */
    public function optimizeClear(): JsonResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Application cache cleared successfully.',
                null,
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            Artisan::call('optimize:clear');

            return response()->json([
                'status' => true,
                'message' => 'Application cache cleared successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to clear cache.',
            ], 500);
        }
    }

    /**
     * Cache the config files.
     *
     * @return JsonResponse
     */
    public function configCache(): JsonResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Config cached successfully.',
                null,
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            Artisan::call('config:cache');

            return response()->json([
                'status' => true,
                'message' => 'Config cached successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to cache config.',
            ], 500);
        }
    }

    /**
     * Run pending migrations.
     *
     * @return JsonResponse
     */
    public function runMigrate(): JsonResponse
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Migrations executed successfully.',
                null,
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            Artisan::call('migrate', ['--force' => true]);

            return response()->json([
                'status' => true,
                'message' => 'Migrations executed successfully.',
                'output' => Artisan::output(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run migrate:fresh with seeders (drops all tables, re-migrates, and seeds).
     * Forces logout since all data including sessions/users gets wiped.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function runMigrateFreshSeed(Request $request): JsonResponse
    {
        // Extra safety: block on production unless explicitly allowed
        if (app()->environment('production') && !config('app.allow_migrate_fresh_in_production', false)) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Migrate fresh is disabled in production.',
                null,
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );
            return response()->json([
                'status' => false,
                'message' => 'Migrate fresh is disabled in production.',
            ], 403);
        }

        try {
            Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            $output = Artisan::output();

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Setting',
                'Database refreshed and seeded successfully. Redirecting to login...',
                null,
                [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Force Logout
            |--------------------------------------------------------------------------
            | migrate:fresh wipes users + sessions tables, so the current
            | authenticated session is no longer valid. Explicitly log out
            | to clear guard state and invalidate the session/cookie cleanly.
            */
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'status' => true,
                'message' => 'Database refreshed and seeded successfully. Redirecting to login...',
                'redirect' => route('admin.login'),
                'output' => $output,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Migrate fresh with seed failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Setup 2FA for the user.
     *
     * @return JsonResponse
     */
    public function twoFaSetup(): JsonResponse
    {
        $user = Auth::user();

        $secret = $this->googleTwoFactorService->generateSecretKey();

        session(['2fa_setup_secret' => $secret]);

        $qrCodeSvg = $this->googleTwoFactorService->getQrCodeSvg(
            config('app.name'),
            $user->email,
            $secret
        );

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Setting',
            'Setup 2FA for user: ' . $user->name,
            $user,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'qr_code' => $qrCodeSvg,
            'secret' => $secret,
        ]);
    }

    /**
     * Enable 2FA for the user.
     *
     * @param EnableGoogle2faRequest $request
     *
     * @return JsonResponse
     */
    public function twoFaEnable(EnableGoogle2faRequest $request): JsonResponse
    {
        $secret = session('2fa_setup_secret');

        if (!$secret) {
            return response()->json([
                'success' => false,
                'message' => 'Setup session expired, please scan the QR code again.',
            ], 422);
        }

        $isValid = $this->googleTwoFactorService->verifyKey(
            $secret,
            $request->input('one_time_password')
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'The code you entered is incorrect.',
            ], 422);
        }

        $user = Auth::user();
        $user->google2fa_secret = $secret;
        $user->google2fa_enabled = true;
        $user->google2fa_enabled_at = now();
        $user->save();

        session()->forget('2fa_setup_secret');

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Auth',
            'Google 2FA enabled.',
            $user,
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Google Authenticator has been enabled for your account.',
        ]);
    }

    /**
     * Disable 2FA for the user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function twoFaDisable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => [
                'required',
                'current_password:web',
            ],
        ]);

        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->google2fa_enabled = false;
        $user->google2fa_enabled_at = null;
        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Auth',
            'Google 2FA disabled.',
            $user,
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Google Authenticator has been disabled for your account.',
        ]);
    }

    /**
     * Update Email (SMTP) settings in database settings table.
     *
     * @param EmailSettingRequest $request
     *
     * @return JsonResponse
     */
    public function updateEmailSettings(EmailSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $existingEmail = $this->settingRepository->getSettingArray('mail');

        $rawPassword = (string) ($validated['mail_password'] ?? '');
        if (!empty($rawPassword)) {
            $mailPassword = Crypt::encryptString($rawPassword);
        } else {
            $mailPassword = $existingEmail['mail_password'] ?? '';
        }

        $payload = [
            'mail_mailer' => (string) ($validated['mail_mailer'] ?? 'smtp'),
            'mail_host' => (string) ($validated['mail_host'] ?? ''),
            'mail_port' => (string) ($validated['mail_port'] ?? '587'),
            'mail_encryption' => (string) ($validated['mail_encryption'] ?? 'tls'),
            'mail_username' => (string) ($validated['mail_username'] ?? ''),
            'mail_password' => $mailPassword,
            'mail_from_address' => (string) ($validated['mail_from_address'] ?? ''),
            'mail_from_name' => (string) ($validated['mail_from_name'] ?? ''),
        ];

        $setting = $this->settingRepository->saveSetting('mail', $payload);
        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Setting',
            'Updated Email (SMTP) Settings successfully.',
            $setting,
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Email (SMTP) Settings updated successfully!',
            'data' => $payload,
        ]);
    }

    /**
     * Update OTP settings in database settings table.
     *
     * @param OtpSettingRequest $request
     *
     * @return JsonResponse
     */
    public function updateOtpSettings(OtpSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $existingOtp = $this->settingRepository->getSettingArray('otp');

        $payload = [
            'max_time' => (int) $validated['otp_max_time'],
            'otp_length' => (int) $validated['otp_length'],
            'is_default' => (bool) $validated['otp_is_default'],
            'default' => (string) ( $validated['otp_default'] ?? $existingOtp['default'] ?? '' ),
        ];

        $setting =   $this->settingRepository->saveSetting('otp', $payload);
        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Setting',
            'Updated OTP Settings successfully.',
            $setting,
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $setting = $this->settingRepository->saveSetting('otp', $payload);
        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Setting',
            'Updated OTP Settings successfully.',
            $setting,
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'OTP Settings updated successfully!',
            'data' => $payload,
        ]);
    }

    /**
     * Update Twilio settings in database settings table.
     *
     * @param TwilioSettingRequest $request
     *
     * @return JsonResponse
     */
    public function updateTwilioSettings(TwilioSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $existingTwilio = $this->settingRepository->getSettingArray('twilio');

        $rawToken = (string) ($validated['twilio_auth_token'] ?? '');
        if (!empty($rawToken)) {
            $twilioAuthToken = Crypt::encryptString($rawToken);
        } else {
            $twilioAuthToken = $existingTwilio['twilio_auth_token'] ?? '';
        }

        $payload = [
            'twilio_account_sid' => (string) ($validated['twilio_account_sid'] ?? ''),
            'twilio_auth_token' => $twilioAuthToken,
            'twilio_from_number' => (string) ($validated['twilio_from_number'] ?? ''),
        ];

        $setting = $this->settingRepository->saveSetting('twilio', $payload);
        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Setting',
            'Updated Twilio SMS Settings successfully.',
            $setting,
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Twilio SMS Settings updated successfully!',
            'data' => $payload,
        ]);
    }

    /**
     * Update AWS Cloud settings in database settings table.
     *
     * @param AwsSettingRequest $request
     *
     * @return JsonResponse
     */
    public function updateAwsSettings(AwsSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $existingAws = $this->settingRepository->getSettingArray('aws');

        $rawSecret = (string) ($validated['aws_secret_access_key'] ?? '');
        if (!empty($rawSecret)) {
            $awsSecretKey = Crypt::encryptString($rawSecret);
        } else {
            $awsSecretKey = $existingAws['aws_secret_access_key'] ?? '';
        }

        $payload = [
            'aws_access_key_id' => (string) ($validated['aws_access_key_id'] ?? ''),
            'aws_secret_access_key' => $awsSecretKey,
            'aws_default_region' => (string) ($validated['aws_default_region'] ?? 'us-east-1'),
            'aws_bucket' => (string) ($validated['aws_bucket'] ?? ''),
        ];

        $setting = $this->settingRepository->saveSetting('aws', $payload);
        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Setting',
            'Updated AWS Cloud Settings successfully.',
            $setting,
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'AWS Cloud Settings updated successfully!',
            'data' => $payload,
        ]);
    }
}
