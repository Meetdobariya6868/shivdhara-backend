<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;
use App\Domain\Enums\UserRole;
use App\Domain\Enums\UserStatus;
use App\Models\Customer;
use App\Models\DesignVariant;
use App\Models\Order;
use App\Models\OrderCategory;
use App\Models\OrderType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 10 salesmen, each with a couple of demo orders (rooms + items).
 *
 * Every write is idempotent: salesmen are keyed by mobile number, customers by
 * contact, and a salesman's orders are only created when they currently have
 * none — so the seeder can be re-run safely without duplicating data.
 */
class DemoSalesmenSeeder extends Seeder
{
    private const COUNT = 10;
    private const ORDERS_PER_SALESMAN = 2;

    /** @var list<string> */
    private const NAMES = [
        'Rohit Verma', 'Anjali Rao', 'Vikram Singh', 'Sneha Kulkarni', 'Karan Malhotra',
        'Divya Iyer', 'Manish Gupta', 'Pooja Reddy', 'Suresh Nair', 'Farah Khan',
    ];

    private const ROOM_NAMES = ['Living Room', 'Bedroom', 'Kitchen', 'Hall'];

    public function run(): void
    {
        $admin = User::where('role', UserRole::Admin->value)->first();
        if ($admin === null) {
            $this->command?->warn('DemoSalesmenSeeder skipped: no admin found (run UserSeeder first).');

            return;
        }

        $categories = OrderCategory::all();
        $types      = OrderType::all();
        $variants   = DesignVariant::where('is_active', true)->get();

        if ($categories->isEmpty() || $types->isEmpty() || $variants->isEmpty()) {
            $this->command?->warn('DemoSalesmenSeeder skipped: seed categories, types and catalogue first.');

            return;
        }

        for ($n = 1; $n <= self::COUNT; $n++) {
            $salesman = User::firstOrCreate(
                ['mobile_number' => '910000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT)],
                [
                    'name'              => self::NAMES[$n - 1],
                    'password'          => Hash::make('password'),
                    'role'              => UserRole::Salesman->value,
                    'status'            => UserStatus::Active->value,
                    'can_create_orders' => true,
                    'created_by_id'     => $admin->id,
                ],
            );

            // Idempotent: skip order seeding for a salesman that already has orders.
            if ($salesman->orders()->exists()) {
                continue;
            }

            for ($j = 1; $j <= self::ORDERS_PER_SALESMAN; $j++) {
                $this->seedOrder($salesman, (int) $admin->id, $n, $j, $categories, $types, $variants);
            }
        }
    }

    /**
     * @param  Collection<int, OrderCategory>  $categories
     * @param  Collection<int, OrderType>  $types
     * @param  Collection<int, DesignVariant>  $variants
     */
    private function seedOrder(
        User $salesman,
        int $adminId,
        int $n,
        int $j,
        Collection $categories,
        Collection $types,
        Collection $variants,
    ): void {
        DB::transaction(function () use ($salesman, $adminId, $n, $j, $categories, $types, $variants): void {
            $seq = $n * 10 + $j;

            $customer = Customer::firstOrCreate(
                ['contact' => '93'.str_pad((string) $seq, 8, '0', STR_PAD_LEFT)],
                ['name' => "{$salesman->name} Customer {$j}", 'created_by_id' => $adminId],
            );

            $category = $categories[($n + $j) % $categories->count()];
            $type     = $types[($n + $j) % $types->count()];

            $order = Order::create([
                'order_date'            => now()->subDays(($n - 1) * 4 + $j)->toDateString(),
                'customer_id'           => $customer->id,
                'order_category_id'     => $category->id,
                'order_type_id'         => $type->id,
                'creator_id'            => $salesman->id,
                'advance_payment'       => 1000 * $j,
                'transportation_charge' => 500 * $j,
                'grand_total'           => 0, // recomputed from items below
            ]);

            $roomCount  = 1 + ($j % 2); // 1 or 2 rooms
            $grandTotal = 0.0;

            for ($r = 0; $r < $roomCount; $r++) {
                $room = $order->rooms()->create([
                    'room_name'  => self::ROOM_NAMES[$r] ?? 'Room '.($r + 1),
                    'sort_order' => $r,
                ]);

                $itemCount = 1 + (($n + $r) % 2); // 1 or 2 items
                for ($it = 0; $it < $itemCount; $it++) {
                    $variant = $variants[($seq + $r + $it) % $variants->count()];
                    $isBox   = (($seq + $it) % 2) === 0;
                    $qty     = 5 + (($n + $it) % 8);
                    $ppb     = $isBox ? 2 + ($it % 3) : null;
                    $price   = (float) $variant->sell_rate;
                    $pieces  = $isBox ? $qty * (int) $ppb : $qty;
                    $total   = round($price * $pieces, 2);
                    $grandTotal += $total;

                    $room->items()->create([
                        'design_variant_id' => $variant->id,
                        'item_type'         => $isBox ? ItemType::Box->value : ItemType::Piece->value,
                        'quantity'          => $qty,
                        'pieces_per_box'    => $ppb,
                        'measurement_unit'  => MeasurementUnit::Inch->value,
                        'height'            => 24.000,
                        'width'             => 24.000,
                        'sqft_rate'         => $variant->sell_rate,
                        'price_per_item'    => $price,
                        'product_total'     => $total,
                        'sort_order'        => $it,
                    ]);
                }
            }

            $order->update([
                'grand_total' => round($grandTotal + (float) $order->transportation_charge, 2),
            ]);
        });
    }
}
