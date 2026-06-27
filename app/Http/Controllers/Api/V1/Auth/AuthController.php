<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Application\DTOs\Auth\LoginDTO;
use App\Application\Services\Auth\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Authenticate with mobile number and password.
     * Returns a Sanctum bearer token on success.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            LoginDTO::fromArray($request->validated()),
        );

        return $this->success(
            data: AuthResource::make($result),
            message: 'Login successful.',
        );
    }

    /**
     * Revoke the current bearer token (logout from this device only).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->message('Logged out successfully.');
    }

    /**
     * Return the currently authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            data: UserResource::make($request->user()),
            message: 'User profile retrieved.',
        );
    }
}
