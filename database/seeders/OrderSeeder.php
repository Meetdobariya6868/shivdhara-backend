<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Models\Customer;
use App\Models\DesignVariant;
use App\Models\Order;
use App\Models\OrderCategory;
use App\Models\OrderRoom;
use App\Models\OrderType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('mobile_number', '9000000001')->firstOrFail();
        $salesman = User::where('mobile_number', '9000000002')->firstOrFail();

        $marble  = OrderCategory::where('name', 'marble')->firstOrFail();
        $granito = OrderCategory::where('name', 'granito')->firstOrFail();
        $local   = OrderType::where('name', 'local')->firstOrFail();
        $arch    = OrderType::where('name', 'architect')->firstOrFail();

        $c1 = Customer::where('contact', '9876543201')->firstOrFail();
        $c2 = Customer::where('contact', '9876543203')->firstOrFail();
        $c3 = Customer::where('contact', '9876543205')->firstOrFail();

        $v1 = DesignVariant::where('size', '24x24')->where('finish', 'Glossy')->where('thickness', '9mm')->first();
        $v2 = DesignVariant::where('size', '32x32')->where('finish', 'Matt')->where('thickness', '9mm')->first();
        $v3 = DesignVariant::where('size', '24x48')->where('finish', 'Polished')->where('thickness', '10mm')->first();
        $v4 = DesignVariant::where('size', '12x12')->where('finish', 'Glossy')->where('thickness', '7mm')->first();

        // ── Order 1 ───────────────────────────────────────────────────────
        DB::transaction(function () use ($admin, $c1, $marble, $local, $v1, $v2) {
            if (Order::where('order_number', 'SHV-2026-0001')->exists()) {
                return;
            }

            $order = Order::create([
                'order_number'          => 'SHV-2026-0001',
                'order_date'            => '2026-06-01',
                'customer_id'           => $c1->id,
                'order_category_id'     => $marble->id,
                'order_type_id'         => $local->id,
                'creator_id'            => $admin->id,
                'advance_payment'       => 5000.00,
                'transportation_charge' => 500.00,
                'total_purchase_amount' => 10858.76,
                'total_sell_amount'     => 14919.24,
                'total_profit'          => 4060.48,
                'grand_total'           => 15419.24,
                'balance_due'           => 10419.24,
            ]);

            $room1 = OrderRoom::create([
                'order_id'      => $order->id,
                'room_name'     => 'Living Room',
                'sort_order'    => 0,
                'total_sqft'    => 160.00,
                'total_purchase'=> 6080.00,
                'total_sell'    => 8320.00,
                'total_profit'  => 2240.00,
            ]);

            $room1->items()->create([
                'design_variant_id' => $v1?->id,
                'item_type'         => ItemType::Box->value,
                'pieces_per_box'    => 4,
                'number_of_boxes'   => 10,
                'measurement_unit'  => MeasurementUnit::Inch->value,
                'height'            => 24.000,
                'width'             => 24.000,
                'area_sqft'         => 4.0000,
                'total_pieces'      => 40,
                'total_sqft'        => 160.0000,
                'purchase_rate'     => 38.00,
                'sell_rate'         => 52.00,
                'purchase_amount'   => 6080.00,
                'sell_amount'       => 8320.00,
                'profit'            => 2240.00,
                'sort_order'        => 0,
            ]);

            $room2 = OrderRoom::create([
                'order_id'      => $order->id,
                'room_name'     => 'Bedroom',
                'sort_order'    => 1,
                'total_sqft'    => 113.78,
                'total_purchase'=> 4778.76,
                'total_sell'    => 6599.24,
                'total_profit'  => 1820.48,
            ]);

            $room2->items()->create([
                'design_variant_id' => $v2?->id,
                'item_type'         => ItemType::Box->value,
                'pieces_per_box'    => 2,
                'number_of_boxes'   => 8,
                'measurement_unit'  => MeasurementUnit::Inch->value,
                'height'            => 32.000,
                'width'             => 32.000,
                'area_sqft'         => 7.1111,
                'total_pieces'      => 16,
                'total_sqft'        => 113.7778,
                'purchase_rate'     => 42.00,
                'sell_rate'         => 58.00,
                'purchase_amount'   => 4778.67,
                'sell_amount'       => 6599.11,
                'profit'            => 1820.44,
                'sort_order'        => 0,
            ]);
        });

        // ── Order 2 ───────────────────────────────────────────────────────
        DB::transaction(function () use ($salesman, $c2, $granito, $arch, $v3) {
            if (Order::where('order_number', 'SHV-2026-0002')->exists()) {
                return;
            }

            $order = Order::create([
                'order_number'          => 'SHV-2026-0002',
                'order_date'            => '2026-06-10',
                'customer_id'           => $c2->id,
                'order_category_id'     => $granito->id,
                'order_type_id'         => $arch->id,
                'creator_id'            => $salesman->id,
                'advance_payment'       => 10000.00,
                'transportation_charge' => 1200.00,
                'notes'                 => 'Full home renovation via architect referral.',
                'total_purchase_amount' => 12480.00,
                'total_sell_amount'     => 16896.00,
                'total_profit'          => 4416.00,
                'grand_total'           => 18096.00,
                'balance_due'           => 8096.00,
            ]);

            $room = OrderRoom::create([
                'order_id'      => $order->id,
                'room_name'     => 'Hall',
                'sort_order'    => 0,
                'total_sqft'    => 192.00,
                'total_purchase'=> 12480.00,
                'total_sell'    => 16896.00,
                'total_profit'  => 4416.00,
            ]);

            $room->items()->create([
                'design_variant_id' => $v3?->id,
                'item_type'         => ItemType::Box->value,
                'pieces_per_box'    => 2,
                'number_of_boxes'   => 12,
                'measurement_unit'  => MeasurementUnit::Inch->value,
                'height'            => 24.000,
                'width'             => 48.000,
                'area_sqft'         => 8.0000,
                'total_pieces'      => 24,
                'total_sqft'        => 192.0000,
                'purchase_rate'     => 65.00,
                'sell_rate'         => 88.00,
                'purchase_amount'   => 12480.00,
                'sell_amount'       => 16896.00,
                'profit'            => 4416.00,
                'sort_order'        => 0,
            ]);
        });

        // ── Order 3 (piece-type items) ────────────────────────────────────
        DB::transaction(function () use ($admin, $c3, $marble, $local, $v4) {
            if (Order::where('order_number', 'SHV-2026-0003')->exists()) {
                return;
            }

            $order = Order::create([
                'order_number'          => 'SHV-2026-0003',
                'order_date'            => '2026-06-20',
                'customer_id'           => $c3->id,
                'order_category_id'     => $marble->id,
                'order_type_id'         => $local->id,
                'creator_id'            => $admin->id,
                'advance_payment'       => 0.00,
                'transportation_charge' => 0.00,
                'total_purchase_amount' => 1080.00,
                'total_sell_amount'     => 1560.00,
                'total_profit'          => 480.00,
                'grand_total'           => 1560.00,
                'balance_due'           => 1560.00,
            ]);

            $room = OrderRoom::create([
                'order_id'      => $order->id,
                'room_name'     => 'Kitchen',
                'sort_order'    => 0,
                'total_sqft'    => 60.00,
                'total_purchase'=> 1080.00,
                'total_sell'    => 1560.00,
                'total_profit'  => 480.00,
            ]);

            $room->items()->create([
                'design_variant_id' => $v4?->id,
                'item_type'         => ItemType::Piece->value,
                'number_of_pieces'  => 60,
                'measurement_unit'  => MeasurementUnit::Inch->value,
                'height'            => 12.000,
                'width'             => 12.000,
                'area_sqft'         => 1.0000,
                'total_pieces'      => 60,
                'total_sqft'        => 60.0000,
                'purchase_rate'     => 18.00,
                'sell_rate'         => 26.00,
                'purchase_amount'   => 1080.00,
                'sell_amount'       => 1560.00,
                'profit'            => 480.00,
                'sort_order'        => 0,
            ]);
        });
    }
}
