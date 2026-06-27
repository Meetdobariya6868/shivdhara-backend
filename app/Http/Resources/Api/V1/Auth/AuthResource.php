<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Auth;

use App\Application\DTOs\Auth\AuthResult;
use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AuthResult
 */
final class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token'      => $this->token,
            'token_type' => 'Bearer',
            'user'       => UserResource::make($this->user),
        ];
    }
}
