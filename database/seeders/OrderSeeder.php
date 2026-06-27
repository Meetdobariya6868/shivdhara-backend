<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\OrderStatus;
use App\Domain\Enums\OrderType;
use App\Models\Architect;
use App\Models\Customer;
use App\Models\DesignCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('phone', '9000000001')->first();
        $salesman = User::where('phone', '9000000002')->first();

        $customers  = Customer::all()->keyBy('phone');
        $architects = Architect::all();
        $designs    = DesignCode::all()->keyBy('design_code');

        $orders = [
            // ── Order 1: Local, Delivered ────────────────────────────────
            [
                'meta' => [
                    'order_number'          => 'SHV-2026-0001',
                    'user_id'               => $admin->id,
                    'customer_id'           => $customers['9876543201']->id,
                    'order_type'            => OrderType::Local->value,
                    'order_status'          => OrderStatus::Delivered->value,
                    'transportation_charge' => 500.00,
                    'advance_amount'        => 5000.00,
                    'discount_amount'       => 0.00,
                    'order_date'            => '2026-06-01',
                    'notes'                 => 'Living room and bedroom flooring.',
                ],
                'items' => [
                    [
                        'design_code'   => 'KAJ-VT-2424-001',
                        'area_name'     => 'Living Room',
                        'items_type'    => 0,
                        'quantity'      => 10,
                        'piece_per_box' => 4,
                        'total_pieces'  => 40,
                        'height'        => 24,
                        'width'         => 24,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 4.00,
                        'total_sqft'    => 160.00,
                        'sqft_rate'     => 52.00,
                        'cost_rate'     => 38.00,
                        'unit_price'    => 208.00,
                        'cost_total'    => 6080.00,
                        'line_total'    => 8320.00,
                        'profit_amount' => 2240.00,
                        'item_index'    => 0,
                    ],
                    [
                        'design_code'   => 'KAJ-VT-3232-002',
                        'area_name'     => 'Bedroom',
                        'items_type'    => 0,
                        'quantity'      => 8,
                        'piece_per_box' => 2,
                        'total_pieces'  => 16,
                        'height'        => 32,
                        'width'         => 32,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 7.11,
                        'total_sqft'    => 113.78,
                        'sqft_rate'     => 58.00,
                        'cost_rate'     => 42.00,
                        'unit_price'    => 411.78,
                        'cost_total'    => 4778.76,
                        'line_total'    => 6599.24,
                        'profit_amount' => 1820.48,
                        'item_index'    => 1,
                    ],
                ],
                'status_history' => [
                    ['old' => OrderStatus::Draft,      'new' => OrderStatus::Confirmed,   'remarks' => 'Order confirmed by admin'],
                    ['old' => OrderStatus::Confirmed,  'new' => OrderStatus::Processing,  'remarks' => 'Material dispatched from warehouse'],
                    ['old' => OrderStatus::Processing, 'new' => OrderStatus::Dispatched,  'remarks' => 'Dispatched via transport'],
                    ['old' => OrderStatus::Dispatched, 'new' => OrderStatus::Delivered,   'remarks' => 'Delivered to customer site'],
                ],
            ],

            // ── Order 2: Architect, Confirmed ────────────────────────────
            [
                'meta' => [
                    'order_number'          => 'SHV-2026-0002',
                    'user_id'               => $salesman->id,
                    'customer_id'           => $customers['9876543203']->id,
                    'architect_id'          => $architects->first()?->id,
                    'order_type'            => OrderType::Architect->value,
                    'order_status'          => OrderStatus::Confirmed->value,
                    'transportation_charge' => 1200.00,
                    'advance_amount'        => 10000.00,
                    'discount_amount'       => 500.00,
                    'order_date'            => '2026-06-10',
                    'notes'                 => 'Architect project — full home renovation.',
                ],
                'items' => [
                    [
                        'design_code'   => 'AGI-VT-2448-001',
                        'area_name'     => 'Hall',
                        'items_type'    => 0,
                        'quantity'      => 12,
                        'piece_per_box' => 2,
                        'total_pieces'  => 24,
                        'height'        => 24,
                        'width'         => 48,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 8.00,
                        'total_sqft'    => 192.00,
                        'sqft_rate'     => 88.00,
                        'cost_rate'     => 65.00,
                        'unit_price'    => 704.00,
                        'cost_total'    => 12480.00,
                        'line_total'    => 16896.00,
                        'profit_amount' => 4416.00,
                        'item_index'    => 0,
                    ],
                    [
                        'design_code'   => 'NIT-MB-2448-001',
                        'area_name'     => 'Master Bathroom',
                        'items_type'    => 1,
                        'quantity'      => 10,
                        'piece_per_box' => 2,
                        'total_pieces'  => 10,
                        'height'        => 24,
                        'width'         => 48,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 8.00,
                        'total_sqft'    => 80.00,
                        'sqft_rate'     => 165.00,
                        'cost_rate'     => 120.00,
                        'unit_price'    => 1320.00,
                        'cost_total'    => 9600.00,
                        'line_total'    => 13200.00,
                        'profit_amount' => 3600.00,
                        'item_index'    => 1,
                    ],
                ],
                'status_history' => [
                    ['old' => OrderStatus::Draft, 'new' => OrderStatus::Confirmed, 'remarks' => 'Confirmed after architect approval'],
                ],
            ],

            // ── Order 3: Local, Draft ─────────────────────────────────────
            [
                'meta' => [
                    'order_number'          => 'SHV-2026-0003',
                    'user_id'               => $salesman->id,
                    'customer_id'           => $customers['9876543205']->id,
                    'order_type'            => OrderType::Local->value,
                    'order_status'          => OrderStatus::Draft->value,
                    'transportation_charge' => 0.00,
                    'advance_amount'        => 0.00,
                    'discount_amount'       => 200.00,
                    'order_date'            => '2026-06-20',
                ],
                'items' => [
                    [
                        'design_code'   => 'SOM-CT-1212-001',
                        'area_name'     => 'Kitchen',
                        'items_type'    => 0,
                        'quantity'      => 5,
                        'piece_per_box' => 12,
                        'total_pieces'  => 60,
                        'height'        => 12,
                        'width'         => 12,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 1.00,
                        'total_sqft'    => 60.00,
                        'sqft_rate'     => 26.00,
                        'cost_rate'     => 18.00,
                        'unit_price'    => 26.00,
                        'cost_total'    => 1080.00,
                        'line_total'    => 1560.00,
                        'profit_amount' => 480.00,
                        'item_index'    => 0,
                    ],
                    [
                        'design_code'   => 'SOM-CT-1818-002',
                        'area_name'     => 'Bathroom',
                        'items_type'    => 0,
                        'quantity'      => 3,
                        'piece_per_box' => 6,
                        'total_pieces'  => 18,
                        'height'        => 18,
                        'width'         => 18,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 2.25,
                        'total_sqft'    => 40.50,
                        'sqft_rate'     => 32.00,
                        'cost_rate'     => 22.00,
                        'unit_price'    => 72.00,
                        'cost_total'    => 891.00,
                        'line_total'    => 1296.00,
                        'profit_amount' => 405.00,
                        'item_index'    => 1,
                    ],
                ],
                'status_history' => [],
            ],

            // ── Order 4: Cancelled ────────────────────────────────────────
            [
                'meta' => [
                    'order_number'          => 'SHV-2026-0004',
                    'user_id'               => $admin->id,
                    'customer_id'           => $customers['9876543204']->id,
                    'order_type'            => OrderType::Local->value,
                    'order_status'          => OrderStatus::Cancelled->value,
                    'transportation_charge' => 0.00,
                    'advance_amount'        => 0.00,
                    'discount_amount'       => 0.00,
                    'order_date'            => '2026-06-05',
                    'notes'                 => 'Customer cancelled due to budget revision.',
                ],
                'items' => [
                    [
                        'design_code'   => 'RAK-PS-4848-001',
                        'area_name'     => 'Living Room',
                        'items_type'    => 0,
                        'quantity'      => 5,
                        'piece_per_box' => 1,
                        'total_pieces'  => 5,
                        'height'        => 48,
                        'width'         => 48,
                        'size_unit'     => 'inch',
                        'area_sqft'     => 16.00,
                        'total_sqft'    => 80.00,
                        'sqft_rate'     => 118.00,
                        'cost_rate'     => 85.00,
                        'unit_price'    => 1888.00,
                        'cost_total'    => 6800.00,
                        'line_total'    => 9440.00,
                        'profit_amount' => 2640.00,
                        'item_index'    => 0,
                    ],
                ],
                'status_history' => [
                    ['old' => OrderStatus::Draft,     'new' => OrderStatus::Confirmed,  'remarks' => 'Confirmed initially'],
                    ['old' => OrderStatus::Confirmed, 'new' => OrderStatus::Cancelled,  'remarks' => 'Customer requested cancellation'],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            if (Order::where('order_number', $orderData['meta']['order_number'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($orderData, $admin, $designs) {
                $totalPurchase = 0;
                $totalProfit   = 0;
                $totalPrice    = 0;

                foreach ($orderData['items'] as $item) {
                    $totalPurchase += $item['cost_total'];
                    $totalProfit   += $item['profit_amount'];
                    $totalPrice    += $item['line_total'];
                }

                $meta       = $orderData['meta'];
                $totalPrice = $totalPrice
                    + ($meta['transportation_charge'] ?? 0)
                    - ($meta['discount_amount'] ?? 0);

                $order = Order::create(array_merge($meta, [
                    'total_purchase' => $totalPurchase,
                    'total_profit'   => $totalProfit,
                    'total_price'    => $totalPrice,
                ]));

                foreach ($orderData['items'] as $itemData) {
                    $design = $designs[$itemData['design_code']] ?? null;
                    OrderItem::create(array_merge(
                        collect($itemData)->except('design_code')->toArray(),
                        ['order_id' => $order->id, 'design_code_id' => $design?->id],
                    ));
                }

                foreach ($orderData['status_history'] as $history) {
                    OrderStatusHistory::create([
                        'order_id'   => $order->id,
                        'old_status' => $history['old']->value,
                        'new_status' => $history['new']->value,
                        'changed_by' => $admin->id,
                        'remarks'    => $history['remarks'] ?? null,
                    ]);
                }
            });
        }
    }
}
