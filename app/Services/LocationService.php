<?php

namespace App\Services;

use App\Exceptions\ThirdPartyApiException;
use App\Services\ThirdParty\NominatimClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * LocationService constructor.
     *
     * @param NominatimClient $client
     */
    public function __construct(
        private readonly NominatimClient $client
    ) {
    }

    /**
     * Coordinates to city / state / country. Returns null if not found.
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return array{city: ?string, state: ?string, country: ?string, label: string}|null
     */
    public function reverse(float $latitude, float $longitude): ?array
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        // Round to 2 decimal places
        $latitude = round((float) $latitude, 2);
        $longitude = round((float) $longitude, 2);

        $key = "location.reverse.{$latitude}_{$longitude}";

        // Cache for 5 minutes
        if ($cached = Cache::get($key)) {
            return $cached;
        }

        // Don't cache failed responses too often (5 minutes) to avoid cache poisoning attacks
        if (Cache::has($key . '.failed')) {
            return null;
        }

        try {
            $data = $this->client->reverse($latitude, $longitude);
        } catch (ThirdPartyApiException $e) {
            Log::channel('api')->warning('Reverse geocoding failed: ' . $e->getMessage());
            Cache::put($key . '.failed', true, now()->addMinutes(10));

            return null;
        }

        // Reverse geocoding failed for some reason (e.g. invalid coordinates)
        $address = $data['address'] ?? null;

        if (!$address) {
            Cache::put($key . '.failed', true, now()->addMinutes(10)); // Cache for 10 minutes

            return null;
        }

        $city = $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['state_district']
            ?? $address['county']
            ?? null;

        $location = [
            'city'    => $city,
            'state'   => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
            'postal'  => $address['postcode'] ?? null,
        ];

        $location['label'] = collect($location)->filter()->implode(', ');

        if ($location['label'] === '') {
            return null;
        }

        // Cache for 5 minutes
        Cache::forever($key, $location);

        return $location;
    }
}
