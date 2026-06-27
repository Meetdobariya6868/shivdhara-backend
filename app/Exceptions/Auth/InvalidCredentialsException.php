<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Thrown when mobile number or password does not match any active account.
 * Intentionally generic — never reveal whether the mobile number exists.
 */
final class InvalidCredentialsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Invalid mobile number or password.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
