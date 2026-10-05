<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Method create
     *
     * @param array $data
     *
     * @return User
     */
    public function create(array $data): User;

    /**
     * Method findByEmail
     *
     * @param string $email
     *
     * @return User
     */
    public function findByEmail(string $email): ?User;

    /**
     * Method findAdminByEmail
     *
     * @param string $email
     *
     * @return User
     */
    public function findAdminByEmail(string $email): ?User;

    /**
     * Method findByPhone
     *
     * @param string $phone
     *
     * @return User
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Method findByPhoneWithCountry
     *
     * @param string $phone
     *
     * @return User
     */
    public function findByPhoneWithCountry(string $phone): ?User;

    /**
     * Method to retrieve all users
     *
     * @param string|null $search
     * @param int $perPage
     *
     * @return LengthAwarePaginator
     */
     public function getAllUsers(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Finds a user by ID.
     */
    public function findById(int $id): ?User;

    /**
     * Updates a user.
     */
    public function update(int $id, array $data): bool;

    /**
     * Deletes a user.
     */
    public function delete(int $id): bool;

    /**
     * Update user password.
     *
     * @param User $user
     * @param string $password
     *
     * @return bool
     */
    public function updatePassword(
        User $user,
        string $password
    ): bool;

    /**
     * Update user profile.
     *
     * @param User $user
     * @param array $data
     *
     * @return bool
     */
    public function updateProfile(
        User $user,
        array $data
    ): bool;

    /**
     * Get count by role.
     *
     * @return array
     */
    public function getCountByRole(): array;
}
