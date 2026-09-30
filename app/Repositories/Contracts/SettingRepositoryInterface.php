<?php

namespace App\Repositories\Contracts;

interface SettingRepositoryInterface
{
    /**
     * Get all system settings formatted in array keyed by type.
     *
     * @param ?int $userId
     * @return array
     */
    public function getAllSettingsFormatted(?int $userId = null): array;

    /**
     * Get setting array value by type with fallback defaults.
     *
     * @param string $type
     * @param array $defaults
     * @param ?int $userId
     * @return array
     */
    public function getSettingArray(string $type, array $defaults = [], ?int $userId = null): array;

    /**
     * Save/Update setting by type and user_id.
     * If setting does not exist for this user_id and type, it inserts; if it exists, it updates.
     *
     * @param string $type
     * @param array $data
     * @param ?int $userId
     * @return mixed
     */
    public function saveSetting(string $type, array $data, ?int $userId = null);

    /**
     * Method updateSettings
     *
     * @param array $data
     * @param ?int $userId
     * @return mixed
     */
    public function updateSettings(array $data, ?int $userId = null);
}
