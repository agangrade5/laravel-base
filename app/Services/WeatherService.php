<?php

namespace App\Services;

use App\Exceptions\ThirdPartyApiException;
use App\Models\User;
use App\Services\ThirdParty\OpenMeteoClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /** Do not hit the API again for the specified number of minutes after caching the response. */
    private const CACHE_MINUTES = 30;

    /** Do not retry the API request for the specified number of minutes after a failure. */
    private const FAILED_RETRY_MINUTES = 5;

    /**
     * constructor
     *
     * @param OpenMeteoClient $client
     *
     * @return void
     */
    public function __construct(
        private readonly OpenMeteoClient $client
    ) {
    }

    /**
     * Weather for the user's saved coordinates.
     * Returns null if coordinates are missing or API fails (card hides).
     *
     * @param User $user
     *
     * @return array<string, mixed>|null
     */
    public function forUser(User $user): ?array
    {
        $location = $user->location;

        if (!$location || $location->latitude === null || $location->longitude === null) {
            return null;
        }

        // Round to 2 decimal places (~1 km), nearby users share one cache
        $latitude = round((float) $location->latitude, 2);
        $longitude = round((float) $location->longitude, 2);

        $cacheKey = "dashboard.weather.{$latitude}_{$longitude}";

        // Don't cache failed responses too often (5 minutes) to avoid cache poisoning attacks
        if (Cache::has($cacheKey . '.failed')) {
            return null;
        }

        // Cache for 30 minutes by default (5 minutes for failed responses)
        $weather = Cache::remember(
            $cacheKey, // Cache key
            now()->addMinutes(self::CACHE_MINUTES), // 30 minutes
            fn () => $this->fetch($latitude, $longitude) // Fetch the weather
        );

        // Cache failed responses for 5 minutes
        if ($weather === null) {
            Cache::put($cacheKey . '.failed', true, now()->addMinutes(self::FAILED_RETRY_MINUTES));

            return null; // Failed to fetch
        }

        // Add the location
        $weather['location'] = $location->label ?: null;

        return $weather;
    }

    /**
     * Fetch weather forecast
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return array<string, mixed>|null
     */
    private function fetch(float $latitude, float $longitude): ?array
    {
        try {
            $data = $this->client->getForecast($latitude, $longitude);
        } catch (ThirdPartyApiException $e) {
            Log::channel('api')->warning('Weather fetch failed: ' . $e->getMessage());

            return null;
        }

        $current = $data['current'] ?? null;

        if (!$current) {
            return null;
        }

        [$text, $icon] = $this->describe((int) $current['weather_code']);

        $forecast = [];
        foreach ($data['daily']['time'] ?? [] as $i => $date) {
            [$dayText, $dayIcon] = $this->describe((int) $data['daily']['weather_code'][$i]);

            $forecast[] = [
                'day'   => Carbon::parse($date)->format('D'),
                'icon'  => $dayIcon,
                'label' => $dayText,
                'max'   => round($data['daily']['temperature_2m_max'][$i]),
                'min'   => round($data['daily']['temperature_2m_min'][$i]),
            ];
        }

        return [
            'temp'       => round($current['temperature_2m']),
            'feels_like' => round($current['apparent_temperature']),
            'humidity'   => $current['relative_humidity_2m'],
            'wind'       => round($current['wind_speed_10m']),
            'label'      => $text,
            'icon'       => $icon,
            'forecast'   => $forecast,
        ];
    }

    /**
     * Describe weather code and icon
     *
     * @param int $code
     *
     * @return array{0: string, 1: string}
     */
    private function describe(int $code): array
    {
        return match (true) {
            $code === 0                => ['Clear sky', 'bi-sun'],
            in_array($code, [1, 2])    => ['Partly cloudy', 'bi-cloud-sun'],
            $code === 3                => ['Cloudy', 'bi-cloud'],
            in_array($code, [45, 48])  => ['Fog', 'bi-cloud-fog'],
            $code >= 51 && $code <= 57 => ['Drizzle', 'bi-cloud-drizzle'],
            ($code >= 61 && $code <= 67) || ($code >= 80 && $code <= 82) => ['Rain', 'bi-cloud-rain'],
            ($code >= 71 && $code <= 77) || in_array($code, [85, 86])    => ['Snow', 'bi-cloud-snow'],
            $code >= 95                => ['Thunderstorm', 'bi-cloud-lightning-rain'],
            default                    => ['Unknown', 'bi-cloud'],
        };
    }
}
