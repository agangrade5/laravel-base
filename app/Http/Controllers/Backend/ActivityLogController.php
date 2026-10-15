<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\{ActivityLogRepositoryInterface, SettingRepositoryInterface};
use App\Services\DateTimeService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;
use App\Exports\ActivityLogExport;
use App\Helpers\UtilityHelper;
use App\Http\Requests\Backend\{ExportRequest, BulkDeleteRequest};
use App\Traits\DownloadsExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityLogController extends Controller
{
    use DownloadsExport;

    /**
     * Create a new controller instance.
     *
     * @param ActivityLogRepositoryInterface $activityLogRepository
     * @param SettingRepositoryInterface $settingRepository
     * @param DateTimeService $dateTimeService
     *
     * @return void
     */
    public function __construct(
        private readonly ActivityLogRepositoryInterface $activityLogRepository,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly DateTimeService $dateTimeService
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
                'created_at' => $this->dateTimeService->format($activity->created_at),
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

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Activity Log',
            'Activity log deleted successfully.',
            $user,
            [
                'id' => $id,
                'name' => $user->name,
                'email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Activity log deleted successfully.',
        ]);
    }

    /**
     * Export (filtered) activity logs as xlsx / csv.
     *
     * @param ExportRequest $request
     *
     * @return BinaryFileResponse
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        $user = auth()->user();

        $query = $this->activityLogRepository->getLogsQuery(
            userId: $user->id,
            isAdmin: $user->hasRole('admin'),
            search: $request->exportSearch()
        );

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Activity Log',
            'Activity logs exported successfully.',
            $user,
            [
                'search' => $request->exportSearch() ?? null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return $this->downloadExport(
            new ActivityLogExport($query),
            'activity-logs',
            $request->exportFormat()
        );
    }

    /**
     * Delete multiple activity logs. Only admin can delete.
     *
     * @param BulkDeleteRequest $request
     *
     * @return JsonResponse
     */
    public function bulkDestroy(BulkDeleteRequest $request): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete activity logs.',
            ], 403);
        }

        $deletedCount = $this->activityLogRepository->deleteByIds($request->ids());

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        UtilityHelper::customActivityLog(
            'Activity Log',
            "{$deletedCount} activity " . str('log')->plural($deletedCount) . ' deleted successfully.',
            auth()->user(),
            [
                'ids' => $request->ids(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => "{$deletedCount} activity " . str('log')->plural($deletedCount) . ' deleted successfully.',
            'deleted' => $deletedCount,
        ]);
    }
}
