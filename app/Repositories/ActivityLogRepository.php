<?php

namespace App\Repositories;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;

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
     * Build the filtered activity log query (shared by listing & export).
     *
     * getLogsQuery() -> getLogs()
     *
     * @param int $userId
     * @param bool $isAdmin
     * @param string|null $search
     *
     * @return Builder
     */
    public function getLogsQuery(
        int $userId,
        bool $isAdmin = false,
        ?string $search = null
    ): Builder {
        return Activity::query()
            ->with('causer')
            // Admin sees all logs, normal user sees only own logs
            ->when(!$isAdmin, fn (Builder $q) => $q->where('causer_id', $userId))
            ->when(!empty($search), function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('subject_id', 'like', "%{$search}%")
                        ->orWhereHas('causer', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->orderByDesc('id'); // Latest first by default
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
        return $this->getLogsQuery($userId, $isAdmin, $search)
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

    /**
     * Delete multiple activity logs by IDs.
     *
     * @param array<int, int> $ids
     *
     * @return int Number of deleted rows
     */
    public function deleteByIds(array $ids): int
    {
        return Activity::query()
            ->whereIn('id', $ids)
            ->delete();
    }
}
