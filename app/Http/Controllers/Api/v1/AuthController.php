<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\UtilityHelper;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Http\Requests\Api\v1\Auth\{LoginRequest, RegisterRequest, SendOtpRequest, VerifyOtpRequest};
use App\Http\Requests\Api\v1\Account\ChangePasswordRequest;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\UserDevice;
use App\Notifications\SendOtpNotification;
use App\Repositories\Contracts\{SettingRepositoryInterface, UserRepositoryInterface};
use App\Services\TwilioService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Cache, Log, RateLimiter};

class AuthController extends Controller
{
    use ApiResponse;

    // Maximum number of wrong OTP attempts
    private const MAX_OTP_ATTEMPTS = 3;

    // OTP send limit: 5 requests per 10 minutes (per account + IP)
    private const OTP_SEND_LIMIT = 5; // 5 requests
    private const OTP_SEND_DECAY = 600; // 10 minutes

    // Keep the OTP in the cache for a few seconds after it expires,
    // so we can distinguish between "expired" and "never requested".
    private const OTP_CACHE_GRACE = 300; // 5 minutes

    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     * @param SettingRepositoryInterface $settingRepository
     * @param TwilioService $twilioService
     *
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly TwilioService $twilioService,
    ) {
    }

    /**
     * POST /api/v1/register
     *
     * Register a new user
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Full number: country_code + phone_number
        $phone = $request->input('country_code') . $request->input('phone_number');

        // email check already exists
        if ($this->userRepository->findByEmail($request->input('email'))) {
            return $this->respond(false, 'The email has already been taken.', null, 422);
        }

        // phone check already exists
        if ($this->userRepository->findByPhoneWithCountry($phone)) {
            return $this->respond(false, 'The phone has already been taken.', null, 422);
        }

        // User create
        $user = $this->userRepository->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'country_iso' => $request->input('country_iso'),
            'country_code' => $request->input('country_code'),
            'phone_number' => $request->input('phone_number'),
            'timezone' => $request->input('timezone'),
        ]);

        // Assign role
        $user->assignRole('user');

        // Sync device
        $this->syncDevice($user, $request);

        // Activity Log - User registered
        $this->log('User registered successfully (API).', $user, $request, [
            'device_id' => $request->input('device_id'),
            'device_type' => $request->input('device_type'),
        ]);

        return $this->respond(
            true,
            'Registration successful. Please login to continue.',
            ['user' => UserResource::make($user)->resolve()],
            201
        );
    }

    /**
     * POST /api/v1/login (email or phone)
     *
     * Login = credentials check + OTP send.
     * Token is issued after OTP verification.
     *
     * login
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->processOtpRequest($request, 401);
    }

    /**
     * POST /api/v1/send-otp (send + resend both are same)
     *
     * sendOtp
     *
     * @param SendOtpRequest $request
     *
     * @return JsonResponse
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        return $this->processOtpRequest($request, 404);
    }

    /**
     * POST /api/v1/verify-otp
     *
     * verifyOtp
     *
     * @param VerifyOtpRequest $request
     *
     * @return JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        // Identifier
        [$type, $value] = $this->identifier($request);
        // cache key
        $key = $this->cacheKey($type, $value);

        // OTP data
        $otpData = Cache::get($key);

        // OTP not found
        if (!$otpData) {
            $this->log('OTP verification attempted with no active OTP.', null, $request, [
                'login_type' => $type,
            ]);

            return $this->respond(
                false,
                'OTP has expired. Please request a new OTP.',
                ['reason' => 'otp_expired'],
                422
            );
        }

        // OTP expired check
        if (now()->timestamp > $otpData['expires_at']) {
            Cache::forget($key);
            $this->log('OTP verification attempted with an expired OTP.', null, $request, [
                'login_type' => $type,
                'user_id' => $otpData['user_id'],
            ]);

            return $this->respond(
                false,
                'OTP has expired. Please request a new OTP.',
                ['reason' => 'otp_expired'],
                422
            );
        }

        // Find user by id
        $user = $this->userRepository->findById($otpData['user_id']);

        // User not found
        if (!$user) {
            Cache::forget($key);
            $this->log('OTP verification attempted for a non-existent user.', null, $request, [
                'user_id' => $otpData['user_id'],
            ]);

            return $this->respond(false, 'User account not found.', null, 404);
        }

        // User inactive
        if (!$user->is_active) {
            Cache::forget($key);
            $this->log('OTP verification attempted for an inactive user.', $user, $request);

            return $this->respond(false, 'Your account is inactive. Please contact the administrator.', null, 403);
        }

        // Verify OTP attempts
        $attempts = (int) $otpData['attempts'];

        // OTP attempts exceeded
        if ($attempts >= self::MAX_OTP_ATTEMPTS) {
            $this->log('OTP verification attempt blocked.', $user, $request, [
                'max_attempts' => self::MAX_OTP_ATTEMPTS,
            ]);

            return $this->respond(false, 'Maximum OTP attempts exceeded. Please resend OTP.', [
                'reason' => 'otp_max_attempts',
                'remaining_attempts' => 0
            ], 429);
        }

        // OTP not matched
        if (!hash_equals((string) $otpData['otp'], $this->hashOtp((string) $request->input('otp')))) {
            $attempts++;
            $otpData['attempts'] = $attempts;

            // Update cache
            $ttl = max(1, $otpData['expires_at'] + self::OTP_CACHE_GRACE - now()->timestamp);
            Cache::put($key, $otpData, $ttl);

            // Calculate remaining attempts
            $remaining = max(0, self::MAX_OTP_ATTEMPTS - $attempts);
            $isBlocked = $remaining === 0;

            // Activity log - Invalid OTP
            $this->log(
                $isBlocked
                    ? 'Invalid OTP entered. Maximum attempts reached, verification blocked.'
                    : 'Invalid OTP entered.',
                $user,
                $request,
                [
                    'login_type' => $type,
                    'attempt' => $attempts,
                    'max_attempts' => self::MAX_OTP_ATTEMPTS,
                    'remaining_attempts' => $remaining,
                    'blocked' => $isBlocked,
                ]
            );

            return $isBlocked
                ? $this->respond(
                    false,
                    'Incorrect OTP. Maximum attempts reached. Please resend OTP.',
                    ['reason' => 'otp_max_attempts', 'remaining_attempts' => 0],
                    429
                )
                : $this->respond(
                    false,
                    "Incorrect OTP. {$remaining} attempt" . ($remaining === 1 ? '' : 's') . ' remaining.',
                    ['reason' => 'otp_invalid', 'remaining_attempts' => $remaining],
                    422
                );
        }

        // OTP matched, clear cache
        Cache::forget($key);

        // Set timezone
        UtilityHelper::setUserTimezone($user, $request->input('timezone'));

        /*
        | Device handling:
        |
        | - Revoke the previous token if the same device already has one
        |   (one device = one active session)
        | - Generate a new token and store the token ID in the device record
        */
        $device = $this->syncDevice($user, $request);

        // Revoke previous token
        if ($device->token_id) {
            $user->tokens()->where('id', $device->token_id)->delete();
        }

        // Create new token
        $newToken = $user->createToken($request->input('device_id'));

        // Update device
        $device->update([
            'token_id' => $newToken->accessToken->id,
            'last_login_at' => now(),
            'last_logout_at' => null,
        ]);

        // Activity log - User logged in
        $this->log('User logged in successfully using OTP (API).', $user, $request, [
            'login_type' => $type,
            'device_id' => $device->device_id,
            'device_type' => $device->device_type,
        ]);

        return $this->respond(true, 'Login successful!', [
            'token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'user' => UserResource::make($user)->resolve(),
        ]);
    }

    /**
     * POST /api/v1/change-password
     *
     * changePassword
     *
     * @param ChangePasswordRequest $request
     *
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()->id;

        $this->userRepository->updatePassword($user, $request->validated('password'));

        // Security: Log out from all other devices while keeping the current device logged in.
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        // Update other devices
        UserDevice::where('user_id', $user->id)
            ->where('token_id', '!=', $currentTokenId)
            ->update(['token_id' => null, 'last_logout_at' => now()]);

        // Activity Log - Password changed
        UtilityHelper::customActivityLog(
            'Account',
            'Password changed successfully (API).',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return $this->respond(true, 'Password updated successfully.');
    }

    /**
     * POST /api/v1/logout (auth:sanctum)
     *
     * Logout the user
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Current user
        $user = $request->user();
        // Current token
        $token = $user->currentAccessToken();

        // Activity Log - User logged out
        $this->log('User logged out successfully (API).', $user, $request, [
            'token_id' => $token->id,
        ]);

        // Update token and last_logout_at
        UserDevice::where('user_id', $user->id)
            ->where('token_id', $token->id)
            ->update(['token_id' => null, 'last_logout_at' => now()]);

        // Revoke token
        $token->delete();

        return $this->respond(true, 'You have been logged out successfully.');
    }

    /**
     * processOtpRequest (login + send-otp)
     *
     * @param Request $request
     * @param int $notFoundCode
     *
     * @return JsonResponse
     */
    private function processOtpRequest(Request $request, int $notFoundCode): JsonResponse
    {
        // Identifier
        [$type, $value] = $this->identifier($request);

        // User found
        $user = $this->findUser($type, $value);

        // User not found
        if (!$user) {
            $this->log('OTP requested for a non-existent account.', null, $request, [
                'login_type' => $type,
                'value' => $value,
            ]);

            return $this->respond(false, 'No account found with these details.', null, $notFoundCode);
        }

        // User inactive
        if (!$user->is_active) {
            $this->log('OTP requested for an inactive account.', $user, $request, ['login_type' => $type]);

            return $this->respond(false, 'Your account is inactive. Please contact the administrator.', null, 403);
        }

        // Admin user
        if ($user->hasRole('admin')) {
            $this->log('Admin user attempted to login through OTP.', $user, $request, ['login_type' => $type]);

            return $this->respond(false, 'Admin users cannot login using OTP.', null, 403);
        }

        // rate limit key
        $limiterKey = 'api_otp_send:' . hash('sha256', $type . '|' . $value . '|' . $request->ip());

        // OTP send rate limit check (to avoid unnecessary SMS and email costs)
        if (RateLimiter::tooManyAttempts($limiterKey, self::OTP_SEND_LIMIT)) {
            $this->log('OTP send rate limit exceeded.', $user, $request, ['login_type' => $type]);

            return $this->respond(
                false,
                'Too many requests. Please try again after ' . RateLimiter::availableIn($limiterKey) . ' seconds.',
                null,
                429
            );
        }

        // OTP send rate limit hit
        RateLimiter::hit($limiterKey, self::OTP_SEND_DECAY);

        /*
        |--------------------------------------------------------------------------
        | OTP Settings
        |--------------------------------------------------------------------------
        */
        $otpDetails =
            $this->settingRepository
                ->getSettingArray('otp');

        $maxTime = (int) (
            $otpDetails['max_time'] ?? 90
        );

        /*
        |--------------------------------------------------------------------------
        | Generate OTP
        |--------------------------------------------------------------------------
        */
        $otp = UtilityHelper::generateOtp(
            $otpDetails
        );

        // OTP cache
        Cache::put($this->cacheKey($type, $value), [
            'user_id' => $user->id,
            'otp' => $this->hashOtp($otp),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($maxTime)->timestamp,
        ], $maxTime + self::OTP_CACHE_GRACE);

        /*
        |--------------------------------------------------------------------------
        | Send OTP
        |--------------------------------------------------------------------------
        */
        try {
            if ($type === 'phone') {
                $this->twilioService->sendOtp(
                    phone: $value,
                    otp: $otp,
                    expireTime: $maxTime
                );
            } else {
                $user->notify(
                    new SendOtpNotification(
                        otp: $otp,
                        otpExpireTime: $maxTime
                    )
                );
            }

            // Activity Log - OTP Sent Successfully
            $this->log('OTP sent successfully (API).', $user, $request, ['login_type' => $type]);
        } catch (\Throwable $e) {
            // Clear OTP cache
            Cache::forget($this->cacheKey($type, $value));

            // Log - OTP Delivery Failed
            Log::channel('auth')->error('OTP delivery failed (API).', [
                'user_id' => $user->id,
                'type' => $type,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            // Activity Log - OTP Delivery Failed
            $this->log('OTP delivery failed (API).', $user, $request, ['login_type' => $type]);

            return $this->respond(false, 'Unable to send OTP. Please try again later.', null, 500);
        }

        return $this->respond(true, 'OTP sent successfully.', [
            'expires_in' => $maxTime,
            'max_attempts' => self::MAX_OTP_ATTEMPTS,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Sync device (update or create)
     *
     * @param User $user
     * @param Request $request
     *
     * @return UserDevice
     */
    private function syncDevice($user, Request $request): UserDevice
    {
        return UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $request->input('device_id'),
            ],
            [
                'device_type' => $request->input('device_type'),
                'last_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
    }

    /**
     * Identifier
     *
     * @param Request $request
     *
     * @return array
     */
    private function identifier(Request $request): array
    {
        $type = $request->input('login_type');

        $value = $type === 'email'
            ? $request->input('email')
            : $request->input('country_code') . $request->input('phone');

        return [$type, $value];
    }

    /**
     * Find user by email or phone
     *
     * @param string $type
     * @param string $value
     *
     * @return User|null
     */
    private function findUser(string $type, string $value)
    {
        return $type === 'email'
            ? $this->userRepository->findByEmail($value)
            : $this->userRepository->findByPhoneWithCountry($value);
    }

    /**
     * Cache key
     *
     * @param string $type
     * @param string $value
     *
     * @return string
     */
    private function cacheKey(string $type, string $value): string
    {
        return 'api_login_otp:' . hash('sha256', $type . '|' . $value);
    }

    /**
     * Hash OTP
     *
     * @param string $otp
     *
     * @return string
     */
    private function hashOtp(string $otp): string
    {
        return hash_hmac('sha256', $otp, config('app.key'));
    }

    /**
     * Log activity
     *
     * @param string $message
     * @param ?User $user
     * @param Request $request
     * @param array $extra
     *
     * @return void
     */
    private function log(string $message, $user, Request $request, array $extra = []): void
    {
        UtilityHelper::customActivityLog(
            'Auth',
            $message,
            $user,
            array_merge([
                'user_id' => $user?->id,
                'email' => $user?->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], $extra)
        );
    }
}
