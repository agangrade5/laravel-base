<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
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
    ): Builder;

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

    /**
     * Delete multiple activity logs by IDs.
     *
     * @param array<int, int> $ids
     *
     * @return int Number of deleted rows
     */
    public function deleteByIds(array $ids): int;
}
