<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\Auth\AccountBlockedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures that an already-authenticated user has not been blocked
 * since their token was issued. Revokes the token on block detection
 * so the user cannot make further requests with the same token.
 */
final class EnsureUserIsActive
{
    /**
     * @throws AccountBlockedException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isActive()) {
            $user->currentAccessToken()->delete();
            throw new AccountBlockedException();
        }

        return $next($request);
    }
}
