<?php

declare(strict_types=1);

namespace App\Application\DTOs\User;

use App\Application\DTOs\BaseDTO;

/**
 * Carries validated data for updating a salesman's profile.
 */
final class UpdateUserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $mobileNumber,
        public readonly bool   $canCreateOrders,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) $data['name'],
            mobileNumber: (string) $data['mobile_number'],
            canCreateOrders: (bool) ($data['can_create_orders'] ?? true),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name'              => $this->name,
            'mobile_number'     => $this->mobileNumber,
            'can_create_orders' => $this->canCreateOrders,
        ];
    }
}
