<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserLocationRepositoryInterface;
use Illuminate\Support\Facades\Log;

class UserLocationService
{
    /**
     * UserLocationService constructor.
     *
     * @param UserLocationRepositoryInterface $userLocationRepository
     *
     * @return void
     */
    public function __construct(
        private readonly UserLocationRepositoryInterface $userLocationRepository
    ) {
    }

    /**
     * Save the location submitted from the browser/app (lat/long + address).
     * Never throws, so login/register never fails because of this.
     *
     * @param User $user
     * @param array<string, mixed> $input e.g. $request->only(['latitude', 'longitude', 'city', 'state', 'country', 'postcode'])
     *
     * @return void
     */
    public function sync(User $user, array $input): void
    {
        $latitude = $input['latitude'] ?? null;
        $longitude = $input['longitude'] ?? null;

        // Permission denied: keep the previous location unchanged.
        if ($latitude === null || $latitude === '' || $longitude === null || $longitude === '') {
            return;
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        try {
            // Get existing location
            $existing = $this->userLocationRepository->findByUserId($user->id);

            // Has the user moved (~1 km)?
            $moved = !$existing
                || round((float) $existing->latitude, 2) !== round($latitude, 2)
                || round((float) $existing->longitude, 2) !== round($longitude, 2);

            // If moved, old address must be replaced (even with null),
            // otherwise the old city would stay with the new coordinates.
            $this->userLocationRepository->saveForUser($user, [
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'city'      => $input['city'] ?? null,
                'state'     => $input['state'] ?? null,
                'country'   => $input['country'] ?? null,
                'postcode'  => $input['postcode'] ?? null,
            ], overwriteNulls: $moved);
        } catch (\Throwable $e) {
            Log::channel('api')->warning('User location sync failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
        }
    }
}
