<?php

declare(strict_types=1);

namespace App\Application\DTOs\Order;

use App\Application\DTOs\BaseDTO;

/**
 * Immutable input for one room within an order, holding its name and the
 * collection of items the user added under it.
 */
final class CreateOrderRoomDTO extends BaseDTO
{
    /** @param list<CreateOrderItemDTO> $items */
    public function __construct(
        public readonly string $roomName,
        public readonly int $sortOrder,
        public readonly array $items,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        /** @var list<array<string, mixed>> $rawItems */
        $rawItems = $data['items'] ?? [];

        return new self(
            roomName: (string) $data['room_name'],
            sortOrder: (int) ($data['sort_order'] ?? 0),
            items: array_map(
                static fn (array $item): CreateOrderItemDTO => CreateOrderItemDTO::fromArray($item),
                $rawItems,
            ),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'room_name'  => $this->roomName,
            'sort_order' => $this->sortOrder,
            'items'      => array_map(
                static fn (CreateOrderItemDTO $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
