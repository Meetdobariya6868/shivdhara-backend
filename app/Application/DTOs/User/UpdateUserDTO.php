<?php

declare(strict_types=1);

namespace App\Application\DTOs\User;

use App\Application\DTOs\BaseDTO;

/**
 * Carries validated data for updating a salesman's profile.
 *
 * `canCreateOrders` is a genuine partial field: when the request omits it
 * (validated with `sometimes`), it stays null and is excluded from the update
 * so a profile edit never silently re-grants or revokes the permission. The
 * permission is toggled through its own endpoint (UserService@updatePermissions).
 */
final class UpdateUserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $mobileNumber,
        public readonly ?bool  $canCreateOrders = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) $data['name'],
            mobileNumber: (string) $data['mobile_number'],
            canCreateOrders: array_key_exists('can_create_orders', $data)
                ? (bool) $data['can_create_orders']
                : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $attributes = [
            'name'          => $this->name,
            'mobile_number' => $this->mobileNumber,
        ];

        if ($this->canCreateOrders !== null) {
            $attributes['can_create_orders'] = $this->canCreateOrders;
        }

        return $attributes;
    }
}
