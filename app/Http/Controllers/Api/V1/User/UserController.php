<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Application\DTOs\User\CreateUserDTO;
use App\Application\DTOs\User\UpdateUserDTO;
use App\Application\Services\User\UserService;
use App\Domain\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\User\UpdateStatusRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-only User Management endpoints.
 * Thin orchestration only — all business logic lives in UserService,
 * all authorization in UserPolicy.
 */
final class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * GET /users — full list of salesmen (no pagination; filtered client-side).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $salesmen = $this->userService->listSalesmen(
            filters: [
                'status' => $request->string('status')->toString() ?: null,
            ],
        );

        return $this->success(
            data: UserResource::collection($salesmen),
            message: 'Salesmen retrieved.',
        );
    }

    /**
     * POST /users — create a salesman.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createSalesman(
            CreateUserDTO::fromArray($request->validated()),
            $request->user(),
        );

        return $this->success(
            data: UserResource::make($user),
            message: 'Salesman created successfully.',
            status: Response::HTTP_CREATED,
        );
    }

    /**
     * GET /users/{user} — view a single salesman.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', User::class);

        return $this->success(
            data: UserResource::make($user),
            message: 'Salesman retrieved.',
        );
    }

    /**
     * PUT /users/{user} — update profile.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->updateSalesman(
            $user,
            UpdateUserDTO::fromArray($request->validated()),
        );

        return $this->success(
            data: UserResource::make($updated),
            message: 'Salesman updated successfully.',
        );
    }

    /**
     * PATCH /users/{user}/status — block / unblock.
     */
    public function updateStatus(UpdateStatusRequest $request, User $user): JsonResponse
    {
        $this->authorize('changeStatus', $user);

        $updated = $this->userService->changeStatus(
            $user,
            UserStatus::from($request->validated('status')),
        );

        return $this->success(
            data: UserResource::make($updated),
            message: 'Salesman status updated.',
        );
    }

    /**
     * PATCH /users/{user}/password — admin password reset.
     */
    public function updatePassword(UpdatePasswordRequest $request, User $user): JsonResponse
    {
        $this->userService->resetPassword($user, $request->validated('password'));

        return $this->message('Password reset successfully.');
    }

    /**
     * DELETE /users/{user} — soft delete.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->deleteSalesman($user);

        return $this->message('Salesman deleted successfully.');
    }
}
