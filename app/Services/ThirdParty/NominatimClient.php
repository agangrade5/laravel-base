<?php

namespace App\Services\ThirdParty;
use App\Repositories\Contracts\SettingRepositoryInterface;

class NominatimClient extends BaseApiClient
{
    /** Base URL for the API */
    private const BASE_URL = 'https://nominatim.openstreetmap.org';

    /** Seconds to wait for a response from the API */
    protected int $timeout = 5;

    /** Retry 1 time by default (No retries needed here as the API is stable and consistent) */
    protected int $retries = 1;

    /**
     * constructor
     *
     * @param SettingRepositoryInterface $settingRepository
     *
     * @return void
     */
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository
    ) {
    }

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
     * Nominatim requires an identifying User-Agent with a real contact email.
     * Email is read from the saved Mail Settings (mail_from_address).
     *
     * @return array<string, string>
     */
    protected function headers(): array
    {
        return ['User-Agent' => $this->userAgent()];
    }

    /**
     * User-Agent
     *
     * @return string
     */
    private function userAgent(): string
    {
        $mail = $this->settingRepository->getSettingArray(
            'mail',
            config('constants.settings.mail', [])
        );

        $email = $mail['mail_from_address'] ?? null;

        // If email is empty, fallback to app name
        return $email
            ? config('app.name') . ' (' . $email . ')'
            : (string) config('app.name');
    }

    /**
     * Reverse geocoding
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return array<string, mixed>
     */
    public function reverse(float $latitude, float $longitude): array
    {
        return $this->get('reverse', [
            'lat'             => $latitude,
            'lon'             => $longitude,
            'format'          => 'jsonv2',
            'zoom'            => 10, // city level
            'addressdetails'  => 1,
            'accept-language' => 'en',
        ]);
    }
}
