<?php

namespace App\Services\ThirdParty;

class OpenMeteoClient extends BaseApiClient
{
    /** Base URL for the API */
    private const BASE_URL = 'https://api.open-meteo.com/v1';

    /** Seconds to wait for a response from the API */
    protected int $timeout = 5;

    /** Retry 1 time by default (No retries needed here as the API is stable and consistent) */
    protected int $retries = 1;

    /**
     * Base URL for the API
     *
     * @return string
     */
    protected function baseUrl(): string
    {
        return self::BASE_URL;
    }

    /**
     * Fetch weather forecast
     *
     * @return array<string, mixed>
     */
    public function getForecast(float $latitude, float $longitude, int $days = 5): array
    {
        return $this->get('forecast', [
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'current'       => 'temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code',
            'daily'         => 'weather_code,temperature_2m_max,temperature_2m_min',
            'timezone'      => 'auto',
            'forecast_days' => $days,
        ]);
    }
}
