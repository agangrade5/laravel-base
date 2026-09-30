<?php

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use Twilio\Rest\Client;

class TwilioService
{
    /**
     * Constructor
     *
     * @param SettingRepositoryInterface $settingRepository
     *
     * @return void
     */
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository,
    ) {
    }

    /**
     * Send OTP through Twilio.
     */
    public function sendOtp(
        string $phone,
        string $otp,
        int $expireTime
    ): void {
        $appName = config('app.name');

        $twilioDetails =
            $this->settingRepository->getSettingArray('twilio');

        $sid = $twilioDetails['twilio_account_sid'] ?? null;
        $token = $twilioDetails['twilio_auth_token'] ?? null;
        $from = $twilioDetails['twilio_from_number'] ?? null;

        if (!$sid || !$token || !$from) {
            throw new \RuntimeException(
                'Twilio configuration is incomplete.'
            );
        }

        $client = new Client(
            $sid,
            $token
        );

        $client->messages->create(
            $phone,
            [
                'from' => $from,
                'body' => "Your {$appName} OTP is {$otp}. "
                    . "It is valid for {$expireTime} seconds.",
            ]
        );
    }
}
