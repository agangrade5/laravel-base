<?php

namespace App\Repositories;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    /**
    * Get latest activity logs for dashboard.
    *
    * @param int $userId
    * @param bool $isAdmin
    * @param int $limit
    *
    * @return Collection
    */
    public function getRecentLogs(
        int $userId,
        bool $isAdmin = false,
        int $limit = 5
    ): Collection {
        $query = Activity::query()
            ->with('causer')
            ->latest();

        /*
         * Admin can see all logs.
         * Normal user can see only own logs.
         */
        if (!$isAdmin) {
            $query->where('causer_id', $userId);
        }

        return $query
            ->limit($limit)
            ->get();
    }

    /**
     * Get paginated activity logs.
     *
     * @param int $userId
     * @param bool $isAdmin
     * @param string|null $search
     * @param int $perPage
     *
     * @return LengthAwarePaginator
     */
    public function getLogs(
        int $userId,
        bool $isAdmin = false,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {

        $query = Activity::query()
            ->with('causer')
            ->latest('created_at');

        /*
        |--------------------------------------------------------------------------
        | User Access
        |--------------------------------------------------------------------------
        */
        if (!$isAdmin) {
            $query->where('causer_id', $userId);
        }

        /*
        |--------------------------------------------------------------------------
        | Search All Columns
        |--------------------------------------------------------------------------
        */
        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                // Activity columns
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%");

                // User name + email
                $q->orWhereHas('causer', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

            });
        }

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find activity log by ID.
     *
     * @param int $id
     *
     * @return ?Activity
     */
    public function findById(
        int $id
    ): ?Activity {
        return Activity::query()
            ->with('causer')
            ->find($id);
    }

    /**
     * Delete activity log.
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function delete(
        Activity $activity
    ): bool {
        return (bool) $activity->delete();
    }
}
