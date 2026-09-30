<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\UtilityHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\{ActivityLogRepositoryInterface, SettingRepositoryInterface};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ActivityLogRepositoryInterface $activityLogRepository
     * @param SettingRepositoryInterface $settingRepository
     *
     * @return void
     */
    public function __construct(
        private readonly ActivityLogRepositoryInterface $activityLogRepository,
        private readonly SettingRepositoryInterface $settingRepository
    ) {
    }

    /**
     * Display activity logs.
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(
        Request $request
    ): View {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        $generalSettings = $this->settingRepository->getSettingArray(
            'general',
            config('constants.settings.general', [])
        );

        $logs = $this->activityLogRepository->getLogs(
            userId: $user->id,
            isAdmin: $isAdmin,
            search: $request->input('search'),
            perPage: $generalSettings['pagination_limit']
        );

        return view('backend.activity-logs.index', [
            'title' => 'Activity Logs',
            'user' => $user,
            'logs' => $logs,
        ]);
    }

    /**
     * Show activity log details.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(
        int $id
    ): JsonResponse {

        $activity = $this->activityLogRepository->findById($id);

        $user = auth()->user();

        if (!$activity) {
            return response()->json([
                'status' => false,
                'message' => 'Activity log not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | User can only view own logs
        |--------------------------------------------------------------------------
        */
        if (
            !$user->hasRole('admin') &&
            (int) $activity->causer_id !== (int) $user->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to view this activity log.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer' => $activity->causer?->name ?? 'System',
                'event' => $activity->event,
                'properties' => $activity->properties,
                'created_at' => UtilityHelper::formatDateTime($activity->created_at),
            ],
        ]);
    }

    /**
     * Delete activity log.
     * Only admin can delete.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(
        int $id
    ): JsonResponse {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Only Admin
        |--------------------------------------------------------------------------
        */
        if (!$user->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete activity logs.',
            ], 403);
        }


        $activity = $this->activityLogRepository->findById($id);

        if (!$activity) {
            return response()->json([
                'status' => false,
                'message' => 'Activity log not found.',
            ], 404);
        }


        $deleted = $this->activityLogRepository->delete($activity);

        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to delete activity log.',
            ], 500);
        }


        return response()->json([
            'status' => true,
            'message' => 'Activity log deleted successfully.',
        ]);
    }
}
