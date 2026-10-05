<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MasterController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/phone-country-code
     *
     * Phone country codes
     *
     * @return JsonResponse
     */
    public function phoneCountryCode(): JsonResponse
    {
        $countries = collect(config('countries.countries', []))
            ->map(fn (array $country) => [
                'iso' => $country['iso'],
                'name' => $country['name'],
                'code' => $country['code'],
                'flag' => $country['flag'],
            ])
            ->values();

        return $this->respond(true, 'Phone country codes fetched successfully.', $countries);
    }
}
