<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\UserRole;
use App\Domain\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['mobile_number' => '9000000001'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Admin->value,
                'status'            => UserStatus::Active->value,
                'can_create_orders' => true,
            ],
        );

        $salesmen = [
            ['name' => 'Ravi Sharma',  'mobile_number' => '9000000002', 'can_create_orders' => true],
            ['name' => 'Priya Mehta',  'mobile_number' => '9000000003', 'can_create_orders' => false],
            ['name' => 'Arjun Patel',  'mobile_number' => '9000000004', 'can_create_orders' => true],
        ];

        foreach ($salesmen as $data) {
            User::firstOrCreate(
                ['mobile_number' => $data['mobile_number']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'role'              => UserRole::Salesman->value,
                    'status'            => UserStatus::Active->value,
                    'can_create_orders' => $data['can_create_orders'],
                    'created_by_id'     => $admin->id,
                ],
            );
        }
    }
}
