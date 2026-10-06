<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Helpers\UtilityHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Auth\{LoginRequest, UserLoginRequest, VerifyOtpRequest};
use App\Notifications\SendOtpNotification;
use App\Repositories\Contracts\{UserRepositoryInterface, SettingRepositoryInterface};
use App\Services\TwilioService;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, Log, Session};
use Illuminate\View\View;

class LoginController extends Controller
{
    // Maximum number of wrong OTP attempts
    private const MAX_OTP_ATTEMPTS = 3;

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
     * Method index
     *
     * @return View
     */
    public function index(): View
    {
        return view('backend.auth.login', [
            'title' => 'Login',
            'bodyClassName' => 'login-page'
        ]);
    }

    /**
     * Method userLogin
     *
     * @return View
     */
    public function userLogin(): View
    {
        return view('backend.auth.user-login', [
            'title' => 'Login',
            'bodyClassName' => 'login-page'
        ]);
    }

    /**
     * Method login
     *
     * @param LoginRequest $request
     *
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only([
            'email',
            'password',
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::validate($credentials)) {
            /*
            |--------------------------------------------------------------------------
            | Activity Log - login Failed
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'Failed login attempt.',
                null,
                [
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );
            return back()
                ->withErrors([
                    'email' => 'The provided credentials are incorrect.',
                ])
                ->withInput($request->only('email', 'remember'));
        }


        /*
        |--------------------------------------------------------------------------
        | Check if 2FA is enabled
        |--------------------------------------------------------------------------
        */
        $user = $this->userRepository->findByEmail($request->email);

        if ($user->google2fa_enabled) {
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $remember);

            return redirect()->route('admin.twoFa.show');
        }

        /*
        |--------------------------------------------------------------------------
        | Login user
        |--------------------------------------------------------------------------
        */

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        if (!$user) {
            return back()
                ->withErrors([
                    'email' => 'The provided credentials are incorrect.',
                ])
                ->withInput($request->only('email', 'remember'));
        }

        if (!$user->is_active) {
            return back()
                ->withErrors([
                    'email' => 'Your account is inactive.',
                ])
                ->withInput($request->only('email', 'remember'));
        }

        Auth::login($user, $remember);

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Set timezone
        |--------------------------------------------------------------------------
        */
        UtilityHelper::setUserTimezone(
            $user,
            $request->input('timezone')
        );

        /*
        |--------------------------------------------------------------------------
        | Activity Log - Login Success
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Auth',
            $user->hasRole('admin')
                ? 'Admin logged in successfully.'
                : 'User logged in successfully.',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => $remember,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('admin')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Login successful!');
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Login successful!');
    }

    /**
     * Method sendOtp
     *
     * @param UserLoginRequest $request
     *
     * @return RedirectResponse
     */
    public function sendOtp(
        UserLoginRequest $request
    ): RedirectResponse {

        $type = $request->input('login_type');

        /*
        |--------------------------------------------------------------------------
        | Get Login Value
        |--------------------------------------------------------------------------
        */
        if ($type === 'email') {
            $value = $request->input('email');
        } else {
            $countryCode =
                $request->input('country_code');

            $phone =
                $request->input('phone');

            $value = $countryCode . $phone;
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */
        $user = $type === 'email'
            ? $this->userRepository->findByEmail($value)
            : $this->userRepository->findByPhone($value);

        if (!$user) {
            /*
            |--------------------------------------------------------------------------
            | Activity Log - OTP Requested For Unknown Account
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP requested for a non-existent account.',
                null,
                [
                    'login_type' => $type,
                    'value' => $value,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->withErrors([
                    $type => 'No account found with these details.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Check User Status
        |--------------------------------------------------------------------------
        */
        if (!$user->is_active) {
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP requested for an inactive account.',
                $user,
                [
                    'login_type' => $type,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->withErrors([
                    $type => 'Your account is inactive. Please contact the administrator.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Admin Restriction
        |--------------------------------------------------------------------------
        | Admin can only login through the admin login page.
        | OTP login is not allowed for admin users.
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('admin')) {

            UtilityHelper::customActivityLog(
                'Auth',
                'Admin user attempted to login through OTP.',
                $user,
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'login_type' => $type,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->withErrors([
                    $type => 'Admin users cannot login using OTP.',
                ])
                ->withInput();
        }

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

        /*
        |--------------------------------------------------------------------------
        | Store OTP Session
        |--------------------------------------------------------------------------
        */
        Session::put('login_otp', [
            'user_id' => $user->id,
            'type' => $type,
            'value' => $value,
            'otp' => $otp,
            'timezone' => $request->input('timezone'),
            'expires_at' => now()->addSeconds($maxTime),
            /*
            | Wrong OTP attempts
            */
            'attempts' => 0,
            /*
            | Maximum allowed wrong attempts
            */
            'max_attempts' => self::MAX_OTP_ATTEMPTS,
        ]);

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

            /*
            |--------------------------------------------------------------------------
            | Activity Log - OTP Sent Successfully
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP sent successfully.',
                $user,
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'login_type' => $type,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );
        } catch (\Throwable $e) {
            Log::channel('auth')->error('OTP delivery failed.', [
                'user_id' => $user->id,
                'type' => $type,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Activity Log - OTP Delivery Failed
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP delivery failed.',
                $user,
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'login_type' => $type,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to send OTP. Please try again later.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect Verify Page
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('login.verify')
            ->with(
                'success',
                'OTP sent successfully.'
            );
    }

    /**
     * Method showVerifyOtp
     *
     * @return View|RedirectResponse
     */
    public function showVerifyOtp(): View|RedirectResponse
    {
        if (!session()->has('login_otp')) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'login' =>
                        'Please request a new OTP.',
                ]);
        }

        $otpData = session('login_otp');

        return view('backend.auth.verify-otp', [
            'title' => 'Verify OTP',
            'bodyClassName' => 'login-page',
            'expiresAt' =>
                $otpData['expires_at'],
            'attempts' =>
                $otpData['attempts'] ?? 0,
            'maxAttempts' =>
                $otpData['max_attempts'] ?? self::MAX_OTP_ATTEMPTS,
            'remainingAttempts' =>
                max(
                    0,
                    ($otpData['max_attempts'] ?? self::MAX_OTP_ATTEMPTS)
                    - ($otpData['attempts'] ?? 0)
                ),
        ]);
    }

    /**
     * Method verifyOtp
     *
     * @param VerifyOtpRequest $request
     *
     * @return RedirectResponse
     */
    public function verifyOtp(
        VerifyOtpRequest $request
    ): RedirectResponse {

        $otpData = session('login_otp');

        /*
        |--------------------------------------------------------------------------
        | OTP Session Check
        |--------------------------------------------------------------------------
        */

        if (!$otpData) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log - OTP Session Expired/Missing
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP verification attempted with no active OTP session.',
                null,
                [
                    'user_id' => $otpData['user_id'],
                    'type' => $otpData['type'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'otp' =>'OTP session expired. Please request a new OTP.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */
        $user = $this->userRepository->findById(
            $otpData['user_id']
        );

        if (!$user) {

            UtilityHelper::customActivityLog(
                'Auth',
                'OTP verification attempted for a non-existent user account.',
                null,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            session()->forget('login_otp');

            return back()
                ->withErrors([
                    'otp' => 'OTP verification attempted for a non-existent user account.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Check User Status
        |--------------------------------------------------------------------------
        */
        if (!$user->is_active) {

            UtilityHelper::customActivityLog(
                'Auth',
                'OTP verification attempted for an inactive user account.',
                $user,
                [
                    'user_id' => $user->id,
                    'login_type' => $otpData['type'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            session()->forget('login_otp');

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' => 'Your account is inactive. Please contact the administrator.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Attempts
        |--------------------------------------------------------------------------
        */

        $attempts =
            (int) ($otpData['attempts'] ?? 0);

        $maxAttempts =
            (int) ($otpData['max_attempts'] ?? self::MAX_OTP_ATTEMPTS);

        /*
        |--------------------------------------------------------------------------
        | Already blocked
        |--------------------------------------------------------------------------
        */

        if ($attempts >= $maxAttempts) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log - OTP Verification Blocked
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP verification attempt blocked.',
                $user,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'max_attempts' => $maxAttempts,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->withErrors([
                    'otp' =>
                        'Maximum OTP attempts exceeded. Please resend OTP.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Expiry
        |--------------------------------------------------------------------------
        */

        if (
            now()->greaterThan(
                \Carbon\Carbon::parse(
                    $otpData['expires_at']
                )
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log - OTP Expired
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP expired. Please resend OTP.',
                $user,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->withErrors([
                    'otp' =>
                        'OTP has expired. Please resend OTP.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Submitted OTP
        |--------------------------------------------------------------------------
        */
        $otp =
            implode(
                '',
                $request->input('otp')
            );

        /*
        |--------------------------------------------------------------------------
        | Invalid OTP
        |--------------------------------------------------------------------------
        */
        if (
            !hash_equals(
                (string) $otpData['otp'],
                (string) $otp
            )
        ) {

            $attempts++;

            /*
            | Update session
            */
            session()->put(
                'login_otp.attempts',
                $attempts
            );

            $remaining =
                max(
                    0,
                    $maxAttempts - $attempts
                );

            $isNowBlocked = $attempts >= $maxAttempts;
            /*
            |--------------------------------------------------------------------------
            | Activity Log - Invalid OTP
            | (merged the two separate logs - "Invalid OTP entered" +
            |  "blocked after maximum attempts" - into a single log call
            |  with a status flag, since both fired for the same user action)
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                $isNowBlocked
                    ? 'Invalid OTP entered. Maximum attempts reached, verification blocked.'
                    : 'Invalid OTP entered.',
                $user,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'attempt' => $attempts,
                    'max_attempts' => $maxAttempts,
                    'remaining_attempts' => $remaining,
                    'blocked' => $isNowBlocked,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Maximum Attempts Reached
            |--------------------------------------------------------------------------
            */

            if ($isNowBlocked) {
                return back()
                    ->withErrors([
                        'otp' =>
                            'Invalid OTP. Maximum attempts reached. Please resend OTP.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Attempts Remaining
            |--------------------------------------------------------------------------
            */

            return back()
                ->withErrors([
                    'otp' =>
                        "Invalid OTP. {$remaining} attempt"
                        . ($remaining === 1 ? '' : 's')
                        . " remaining.",
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */
        $user = $this->userRepository->findById(
            $otpData['user_id']
        );

        if (!$user) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log - User Not Found After Valid OTP
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP verified but associated user account no longer exists.',
                $user,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            session()->forget('login_otp');

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' =>
                        'User account not found.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */
        Auth::login($user);

        request()
            ->session()
            ->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Set User Timezone
        |--------------------------------------------------------------------------
        */
        UtilityHelper::setUserTimezone(
            $user,
            $request->input('timezone') ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | Remove OTP Session
        |--------------------------------------------------------------------------
        */
        session()->forget('login_otp');

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Auth',
            'User logged in successfully using OTP.',
            $user,
            [
                'user_id' => $user->id,
                'login_type' => $otpData['type'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Login successful!'
            );
    }

    /**
     * Method resendOtp
     *
     * @return RedirectResponse
     */
    public function resendOtp(): RedirectResponse
    {
        $otpData = session('login_otp');

        /*
        |--------------------------------------------------------------------------
        | OTP Session Check
        |--------------------------------------------------------------------------
        */

        if (!$otpData) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log - Resend Attempted With No OTP Session
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP resend attempted with no active OTP session.',
                null,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' =>
                        'OTP session expired. Please login again.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get OTP Settings
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
        | Generate New OTP
        |--------------------------------------------------------------------------
        */

        $otp = UtilityHelper::generateOtp(
            $otpDetails
        );

        /*
        |--------------------------------------------------------------------------
        | Reset OTP Session
        |--------------------------------------------------------------------------
        */
        session()->put(
            'login_otp',
            [
                'user_id' => $otpData['user_id'],
                'type' => $otpData['type'],
                'value' => $otpData['value'],
                'otp' => $otp,
                'timezone' => $otpData['timezone'] ?? null,
                'expires_at' => now()->addSeconds($maxTime),
                /*
                | Reset wrong attempts
                */
                'attempts' => 0,
                /*
                | Reset max attempts
                */
                'max_attempts' => self::MAX_OTP_ATTEMPTS,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Send New OTP
        |--------------------------------------------------------------------------
        */

        $user = $this->userRepository->findById(
            $otpData['user_id']
        );

        if (!$user) {

            /*
            |--------------------------------------------------------------------------
            | Activity Log - User Not Found On Resend
            |--------------------------------------------------------------------------
            */
            UtilityHelper::customActivityLog(
                'Auth',
                'OTP resend attempted but associated user account no longer exists.',
                null,
                [
                    'user_id' => $otpData['user_id'],
                    'login_type' => $otpData['type'],
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            session()->forget('login_otp');

            return redirect()
                ->route('login')
                ->withErrors([
                    'login' =>
                        'User account not found.',
                ]);
        }

        if ($otpData['type'] === 'phone') {

            $this->twilioService->sendOtp(
                phone: $otpData['value'],
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

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Auth',
            'A new OTP has been sent successfully.',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'login_type' => $otpData['type'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );

        return redirect()
            ->route('login.verify')
            ->with(
                'success',
                'A new OTP has been sent successfully.'
            );
    }

    /**
     * Method logout
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Auth',
            $user->hasRole('admin')
                ? 'Admin logged out successfully.'
                : 'User logged out successfully.',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Check role before logout
        $isAdmin = $user->hasRole('admin');
        return redirect()
            ->route($isAdmin ? 'admin.login' : 'login')
            ->with(
                'success',
                'You have been logged out successfully.'
            );
    }
}
