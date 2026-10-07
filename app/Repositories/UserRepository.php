<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class UserRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param FileUploadService $fileUploadService
     */
    public function __construct(
        private readonly FileUploadService $fileUploadService
    ) {
    }

    /**
     * Method create
     *
     * @param array $data
     *
     * @return User
     */
   public function create(array $data): User
    {
        unset($data['password']);

        return User::create($data);
    }

    /**
     * Method updateUser
     *
     * @param string $email
     *
     * @return User
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Method findAdminByEmail
     *
     * @param string $email
     *
     * @return User
     */
    public function findAdminByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->where('id', User::SUPER_ADMIN)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->first();
    }

    /**
     * Method findByPhone
     *
     * @param string $phone
     *
     * @return User
     */
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone_number', $phone)->first();
    }

    /**
     * Method findByPhoneWithCountry
     *
     * @param string $phone
     *
     * @return User
     */
    public function findByPhoneWithCountry(string $phone): ?User
    {
        return User::whereRaw("CONCAT(country_code, phone_number) = ?", [$phone])->first();
    }

    /**
     * Method to retrieve all users with pagination and optional search.
     *
     * @param string|null $search
     * @param int $perPage
     *
     * @return LengthAwarePaginator
     */
    public function getAllUsers(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::withoutRole('Admin')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Finds a user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Updates a user.
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        unset($data['password']);

        return $user->update($data);
    }

    /**
     * Deletes a user.
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

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
    ): bool {
        return $user->update([
            'password' => Hash::make($password),
        ]);
    }

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
    ): bool {
        // Remove image
        if (!empty($data['remove_profile_image']) && $user->image) {
            $this->fileUploadService->delete($user->image);
            $data['image'] = null;
        }

        // Upload new image (replaceImage() also deletes the old one)
        if (
            isset($data['profile_image']) &&
            $data['profile_image'] instanceof UploadedFile
        ) {
            $data['image'] = $this->fileUploadService->replaceImage(
                $user->image,
                $data['profile_image'],
                "profile-images/users/{$user->id}",
                ['width' => 100, 'height' => 100]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Form Only Field
        |--------------------------------------------------------------------------
        */
        unset($data['remove_profile_image'], $data['profile_image']);

        return $user->update($data);
    }

    /**
     * Get count by role.
     *
     * @return array
     */
    public function getCountByRole(
    ): array {
        $data = [];

        $data['withoutAdminByCount'] = User::where('id', '!=', User::SUPER_ADMIN)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->count();

        $data['roleByCount'] = User::where('id', '!=', User::SUPER_ADMIN)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->with('roles:id,name')
            ->get()
            ->flatMap(function ($user) {
                return $user->roles->pluck('name');
            })
            ->countBy()
            ->toArray();

        return $data;
    }
}
