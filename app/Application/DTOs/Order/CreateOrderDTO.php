<?php

declare(strict_types=1);

namespace App\Application\DTOs\Order;

use App\Application\DTOs\BaseDTO;

/**
 * Immutable input for creating a full order: customer (free text, find-or-created),
 * category/type, charges, optional notes and the ordered list of rooms.
 */
final class CreateOrderDTO extends BaseDTO
{
    /** @param list<CreateOrderRoomDTO> $rooms */
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerContact,
        public readonly int $orderCategoryId,
        public readonly int $orderTypeId,
        public readonly float $advancePayment,
        public readonly float $transportationCharge,
        public readonly ?string $notes,
        public readonly array $rooms,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        /** @var list<array<string, mixed>> $rawRooms */
        $rawRooms = $data['rooms'] ?? [];

        return new self(
            customerName: (string) $data['customer_name'],
            customerContact: (string) $data['customer_contact'],
            orderCategoryId: (int) $data['order_category_id'],
            orderTypeId: (int) $data['order_type_id'],
            advancePayment: (float) ($data['advance_payment'] ?? 0),
            transportationCharge: (float) ($data['transportation_charge'] ?? 0),
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            rooms: array_map(
                static fn (array $room): CreateOrderRoomDTO => CreateOrderRoomDTO::fromArray($room),
                $rawRooms,
            ),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'customer_name'         => $this->customerName,
            'customer_contact'      => $this->customerContact,
            'order_category_id'     => $this->orderCategoryId,
            'order_type_id'         => $this->orderTypeId,
            'advance_payment'       => $this->advancePayment,
            'transportation_charge' => $this->transportationCharge,
            'notes'                 => $this->notes,
            'rooms'                 => array_map(
                static fn (CreateOrderRoomDTO $room): array => $room->toArray(),
                $this->rooms,
            ),
        ];
    }
}
