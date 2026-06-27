<?php

declare(strict_types=1);

namespace App\Application\DTOs\Auth;

use App\Application\DTOs\BaseDTO;

/**
 * Carries validated login credentials from the HTTP layer into AuthService.
 * The service never touches the HTTP Request object directly.
 */
final class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $mobileNumber,
        public readonly string $password,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            mobileNumber: (string) $data['mobile_number'],
            password: (string) $data['password'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'mobile_number' => $this->mobileNumber,
            // password intentionally omitted — DTOs must never serialize credentials
        ];
    }
}
