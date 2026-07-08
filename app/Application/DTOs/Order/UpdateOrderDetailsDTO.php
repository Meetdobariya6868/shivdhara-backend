<?php

declare(strict_types=1);

namespace App\Application\DTOs\Order;

use App\Application\DTOs\BaseDTO;

/**
 * Immutable input for editing an existing order's header fields from the order
 * detail screen: the customer's name/contact, the order category/type, and the
 * order date. The customer edit updates the shared Customer record directly —
 * every other order from the same customer reflects the correction too.
 */
final class UpdateOrderDetailsDTO extends BaseDTO
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerContact,
        public readonly int $orderCategoryId,
        public readonly int $orderTypeId,
        public readonly string $orderDate, // 'Y-m-d'
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            customerName: (string) $data['customer_name'],
            customerContact: (string) $data['customer_contact'],
            orderCategoryId: (int) $data['order_category_id'],
            orderTypeId: (int) $data['order_type_id'],
            orderDate: (string) $data['order_date'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'customer_name'     => $this->customerName,
            'customer_contact'  => $this->customerContact,
            'order_category_id' => $this->orderCategoryId,
            'order_type_id'     => $this->orderTypeId,
            'order_date'        => $this->orderDate,
        ];
    }
}
