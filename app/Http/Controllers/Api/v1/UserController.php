<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\UtilityHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\User\UpdateUserRequest;
use App\Http\Resources\Api\v1\UserResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\{JsonResponse, Request};

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository
     *
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * GET /api/v1/users/{id}
     *
     * User details
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // Check if user is allowed
        if ($denied = $this->denyIfNotAllowed($request, $id)) {
            return $denied;
        }

        // Check if user exists
        $user = $this->userRepository->findById($id);

        // User not found
        if (!$user) {
            return $this->respond(false, 'User not found.', null, 404);
        }

        // Activity Log - User fetched
        UtilityHelper::customActivityLog(
            'User',
            'Fetched user successfully (API).',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        );

        return $this->respond(true, 'User fetched successfully.', [
            'user' => UserResource::make($user)->resolve(),
        ]);
    }

    /**
     * PUT /api/v1/users/{id}
     *
     * name, country_iso, country_code, phone_number, profile_image
     * (PHP does not properly parse multipart form-data with PUT requests,
     * so the client should send a POST request with _method=PUT instead.)
     *
     * Update user
     *
     * @param UpdateUserRequest $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        // Check if user exists or not
        if ($denied = $this->denyIfNotAllowed($request, $id)) {
            return $denied;
        }

        // Check if user exists
        $user = $this->userRepository->findById($id);

        // User not found
        if (!$user) {
            return $this->respond(false, 'User not found.', null, 404);
        }

        // Reuse the same repository method used for the web profile update.
        // It also handles image upload and removal.
        $this->userRepository->updateProfile($user, $request->validated());

        // Get the updated user
        $user = $this->userRepository->findById($id);

        // Activity Log - Profile updated
        UtilityHelper::customActivityLog(
            'User',
            'Profile updated successfully (API).',
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return $this->respond(true, 'Profile updated successfully.', [
            'user' => UserResource::make($user)->resolve(),
        ]);
    }

    /**
     * Only the owner of the record (or an admin) can access it.
     * Check authorization (403) before resource existence (404)
     * to prevent user ID enumeration.
     *
     * @param Request $request
     * @param int $id
     *
     * @return ?JsonResponse
     */
    private function denyIfNotAllowed(Request $request, int $id): ?JsonResponse
    {
        $authUser = $request->user();

        if ($authUser->id !== $id && !$authUser->hasRole('admin')) {
            return $this->respond(false, 'You are not allowed to access this user.', null, 403);
        }

        return null;
    }
}
