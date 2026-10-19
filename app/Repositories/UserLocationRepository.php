<?php

namespace App\Repositories;

use App\Models\{User, UserLocation};
use App\Repositories\Contracts\UserLocationRepositoryInterface;

class UserLocationRepository implements UserLocationRepositoryInterface
{
    /**
     * Find user location by user id.
     *
     * @param int $userId
     *
     * @return ?UserLocation
     */
    public function findByUserId(int $userId): ?UserLocation
    {
        return UserLocation::where('user_id', $userId)->first();
    }

    /**
     * Create or update the user's location.
     *
     * By default null/empty values never overwrite existing data
     * (e.g. permission denied keeps the old location).
     * Pass $overwriteNulls = true when the user has moved, so that
     * the old address is not kept with new coordinates.
     *
     * @param User $user
     * @param array $data
     * @param bool $overwriteNulls
     *
     * @return UserLocation
     */
    public function saveForUser(User $user, array $data, bool $overwriteNulls = false): UserLocation
    {
        $data = collect($data)
            ->only(['latitude', 'longitude', 'city', 'state', 'country', 'postcode'])
            ->when(
                !$overwriteNulls,
                fn ($collection) => $collection->filter(fn ($value) => $value !== null && $value !== '')
            )
            ->all();

        return UserLocation::updateOrCreate(['user_id' => $user->id], $data);
    }
}
