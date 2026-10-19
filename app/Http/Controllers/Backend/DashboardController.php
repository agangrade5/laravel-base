<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\{UserRepositoryInterface, ActivityLogRepositoryInterface};
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param UserRepositoryInterface $userRepository
     * @param ActivityLogRepositoryInterface $activityRepository
     * @param WeatherService $weatherService
     *
     * @return void
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ActivityLogRepositoryInterface $activityRepository,
        protected WeatherService $weatherService
    ) {}

    /**
     * method admin
     *
     * @return View
     */
    public function admin(): View
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Get count by role
        |--------------------------------------------------------------------------
        */
        $getCountByRole = $this->userRepository->getCountByRole();
        $data['roleByCount'] = $getCountByRole['roleByCount'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Get recent activity logs
        |--------------------------------------------------------------------------
        */
        $data['recentActivityLogs'] =
            $this->activityRepository->getRecentLogs(
                $user->id,
                true,
                5
            );

        /*
        |--------------------------------------------------------------------------
        | Get weather
        |--------------------------------------------------------------------------
        */
        $data['weather'] = $this->weatherService->forUser($user);

        $data['title'] = 'Admin Dashboard';

        return view('backend.admin.dashboard', $data);
    }

    /**
     * User Dashboard View
     *
     * @return View
     */
    public function user(): View
    {
        $user = Auth::user();
        /*
        |--------------------------------------------------------------------------
        | Get recent activity logs
        |--------------------------------------------------------------------------
        */
         $data['recentActivityLogs'] =
            $this->activityRepository->getRecentLogs(
                $user->id,
                false,
                5
            );

        /*
        |--------------------------------------------------------------------------
        | Get weather
        |--------------------------------------------------------------------------
        */
        $data['weather'] = $this->weatherService->forUser($user);

        $data['title'] = 'User Dashboard';

        return view('backend.user.dashboard', $data);
    }
}
