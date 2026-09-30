<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * DashboardRepository constructor.
     */
    public function __construct(
    ) {
    }
}
