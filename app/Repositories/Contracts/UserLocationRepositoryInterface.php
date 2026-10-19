<?php

namespace App\Repositories\Contracts;

use App\Models\{User, UserLocation};

interface UserLocationRepositoryInterface
{
    /**
     * Method to find user location by user id
     *
     * @param int $userId
     *
     * @return ?UserLocation
     */
    public function findByUserId(int $userId): ?UserLocation;

    /**
     * Method to save user location
     *
     * @param User $user
     * @param array $data
     * @param bool $overwriteNulls
     *
     * @return UserLocation
     */
    public function saveForUser(User $user, array $data, bool $overwriteNulls = false): UserLocation;
}
