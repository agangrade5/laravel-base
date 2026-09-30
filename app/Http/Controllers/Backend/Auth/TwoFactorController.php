<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Helpers\UtilityHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Auth\VerifyGoogle2faRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\GoogleTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     * @param GoogleTwoFactorService $googleTwoFactorService
     *
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly GoogleTwoFactorService $googleTwoFactorService,
    ) {
    }

    /**
     * Show the "enter your 6-digit code" screen. Only reachable if
     * LoginController::login() already verified the password and
     * put the pending user id in session.
     *
     * @return View|RedirectResponse
     */
    public function twoFaShow(): View|RedirectResponse
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('backend.auth.two-factor', [
            'title' => 'Two Factor Authentication',
            'bodyClassName' => 'login-page',
        ]);
    }

    /**
     * Verify the submitted one-time password and, if correct,
     * actually log the user in.
     *
     * @param VerifyGoogle2faRequest $request
     *
     * @return RedirectResponse
     */
    public function twoFaVerify(VerifyGoogle2faRequest $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('admin.login')
                ->withErrors(['login' => 'Your session has expired, please login again.']);
        }

        $user = $this->userRepository->findById($userId);

        if (!$user || !$user->google2fa_enabled || !$user->google2fa_secret) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);

            return redirect()->route('admin.login')
                ->withErrors(['login' => 'Two factor authentication is not available for this account.']);
        }

        $isValid = $this->googleTwoFactorService->verifyKey(
            $user->google2fa_secret,
            $request->input('one_time_password')
        );

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        if (!$isValid) {
            UtilityHelper::customActivityLog(
                'auth',
                'Failed 2FA verification attempt.',
                $user,
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()->withErrors([
                'one_time_password' => 'The provided code is incorrect or has expired.',
            ]);
        }

        $remember = (bool) $request->session()->get('2fa_remember', false);
        $request->session()->forget(['2fa_user_id', '2fa_remember']);

        Auth::login($user, $remember);

        $request->session()->regenerate();

        UtilityHelper::setUserTimezone($user, $request->input('timezone'));

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'auth',
            $user->hasRole('admin')
                ? 'Admin logged in successfully with 2FA.'
                : 'User logged in successfully with 2FA.',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => $remember,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', 'Login successful!');
        }

        return redirect()->route('dashboard')->with('success', 'Login successful!');
    }
}
