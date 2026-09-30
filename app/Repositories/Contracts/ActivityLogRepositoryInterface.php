<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Activitylog\Models\Activity;

interface ActivityLogRepositoryInterface
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
    ): Collection;

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
    ): LengthAwarePaginator;

    /**
     * Get activity log by ID.
     *
     * @param int $id
     *
     * @return ?Activity
     */
    public function findById(
        int $id
    ): ?Activity;

    /**
     * Delete activity log.
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function delete(
        Activity $activity
    ): bool;
}
