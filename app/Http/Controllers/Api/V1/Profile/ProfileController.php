<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Profile;

use App\Application\DTOs\User\UpdateUserDTO;
use App\Application\Services\User\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\JsonResponse;

/**
 * Self-service profile endpoints for the authenticated user (any role).
 * Thin orchestration only — business logic lives in UserService.
 */
final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * PUT /profile — update the authenticated user's own name + mobile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            UpdateUserDTO::fromArray($request->validated()),
        );

        return $this->success(
            data: UserResource::make($user),
            message: 'Profile updated successfully.',
        );
    }
}
