<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Helpers\UtilityHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Auth\RegisterRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     *
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Method index
     *
     * @return View
     */
    public function index(): View
    {
        return view('backend.auth.register', [
            'title' => 'Register',
            'bodyClassName' => 'register-page'
        ]);
    }

    /**
     * Method register
     *
     * @param RegisterRequest $request
     *
     * @return RedirectResponse
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->userRepository->create(
            $request->validated()
        );

        $user->assignRole('user');

        Auth::login($user);

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Activity Log - Register
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'auth',
            'User registered successfully.',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Registration successful!');
    }
}
