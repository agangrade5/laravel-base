<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\{Crypt, Auth};
use Illuminate\Contracts\Encryption\DecryptException;

class SettingRepository implements SettingRepositoryInterface
{
    /**
     * Helper to safely decrypt a string value if encrypted.
     *
     * @param ?string $value
     * @return string
     */
    public function decryptValue(?string $value): string
    {
        if (empty($value)) {
            return '';
        }
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return $value;
        }
    }

    /**
     * Get all system and user settings formatted in array keyed by type.
     *
     * @param ?int $userId
     *
     * @return array
     */
    public function getAllSettingsFormatted(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        // 1. Global system settings (twilio, mail, aws, otp) where user_id is null
        $globalSettings = Setting::whereNull('user_id')->get()->keyBy('type');

        // System defaults from config/constants.php
        $systemDefaults = config('constants.settings', []);

        $result = [];

        // Process Global System Settings (Twilio, Mail, AWS, OTP)
        foreach ($systemDefaults as $type => $defaultValues) {
            $settingModel = $globalSettings[$type] ?? null;

            if ($settingModel && !empty($settingModel->value)) {
                $decoded = json_decode($settingModel->value, true);
                $merged = is_array($decoded) ? array_merge($defaultValues, $decoded) : $defaultValues;
            } else {
                $merged = $defaultValues;
            }

            // Decrypted values
            if (isset($merged['mail_password'])) {
                $merged['mail_password_decrypted'] = $this->decryptValue($merged['mail_password']);
            }
            if (isset($merged['twilio_auth_token'])) {
                $merged['twilio_auth_token_decrypted'] = $this->decryptValue($merged['twilio_auth_token']);
            }
            if (isset($merged['aws_secret_access_key'])) {
                $merged['aws_secret_access_key_decrypted'] = $this->decryptValue($merged['aws_secret_access_key']);
            }

            $result[$type] = $merged;
        }

        return $result;
    }

    /**
     * Get setting record by type and user_id.
     *
     * @param string $type
     * @param ?int $userId
     *
     * @return ?Setting
     */
    public function getSettingByType(string $type, ?int $userId = null): ?Setting
    {
        // For system settings (twilio, mail, aws, otp)
        return Setting::where('type', $type)
            ->whereNull('user_id')
            ->first() ?? Setting::where('type', $type)->first();
    }

    /**
     * Get setting array value by type with fallback defaults.
     *
     * @param string $type
     * @param array $defaults
     * @param ?int $userId
     *
     * @return array
     */
    public function getSettingArray(string $type, array $defaults = [], ?int $userId = null): array
    {
        $setting = $this->getSettingByType($type, $userId);
        if ($setting && !empty($setting->value)) {
            $decoded = json_decode($setting->value, true);

            if (is_array($decoded)) {
                return array_merge($defaults, $decoded);
            }
        }

        return $defaults;
    }

    /**
     * Save/Update setting by type.
     *
     * @param string $type
     * @param array $data
     * @param ?int $userId
     *
     * @return Setting
     */
    public function saveSetting(string $type, array $data, ?int $userId = null): Setting
    {
        // Global System Setting
        return Setting::updateOrCreate(
            [
                'user_id' => null,
                'type'    => $type,
            ],
            [
                'value' => json_encode($data),
            ]
        );
    }

    /**
     * Updates settings with the given data.
     *
     * @param array $data
     * @param ?int $userId
     */
    public function updateSettings(array $data, ?int $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $settingObj = $userId ? Setting::where('user_id', $userId)->first() : Setting::first();
        if ($settingObj) {
            $settingObj->update($data);
        }
        return $settingObj;
    }
}
