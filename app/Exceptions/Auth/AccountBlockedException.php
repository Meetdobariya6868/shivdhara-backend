<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Thrown when a user provides correct credentials but their account is blocked.
 * Only surfaced after password verification to prevent user-enumeration attacks.
 */
final class AccountBlockedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Your account has been blocked. Please contact the administrator.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_FORBIDDEN);
    }
}
