<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'Admin User',
                'phone'             => '9000000001',
                'email'             => 'admin@shivdhara.com',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Admin->value,
                'is_active'         => true,
                'can_create_orders' => true,
                'login_mode'        => 1,
            ],
            [
                'name'              => 'Ravi Sales',
                'phone'             => '9000000002',
                'email'             => null,
                'password'          => Hash::make('password'),
                'role'              => UserRole::Salesman->value,
                'is_active'         => true,
                'can_create_orders' => true,
                'login_mode'        => 1,
            ],
            [
                'name'              => 'Priya Salesman',
                'phone'             => '9000000003',
                'email'             => null,
                'password'          => Hash::make('password'),
                'role'              => UserRole::Salesman->value,
                'is_active'         => true,
                'can_create_orders' => false,
                'login_mode'        => 1,
            ],
            [
                'name'              => 'Manager User',
                'phone'             => '9000000004',
                'email'             => 'manager@shivdhara.com',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Manager->value,
                'is_active'         => true,
                'can_create_orders' => false,
                'login_mode'        => 1,
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(['phone' => $data['phone']], $data);
        }
    }
}
