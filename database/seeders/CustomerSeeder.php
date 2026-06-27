<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('mobile_number', '9000000001')->firstOrFail();

        $customers = [
            ['name' => 'Rajesh Kumar',  'contact' => '9876543201'],
            ['name' => 'Sunita Patel',  'contact' => '9876543202'],
            ['name' => 'Mohan Shah',    'contact' => '9876543203'],
            ['name' => 'Priti Desai',   'contact' => '9876543204'],
            ['name' => 'Arvind Mehta',  'contact' => '9876543205'],
            ['name' => 'Kavitha Nair',  'contact' => '9876543206'],
            ['name' => 'Deepak Sharma', 'contact' => '9876543207'],
            ['name' => 'Neha Joshi',    'contact' => '9876543208'],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['contact' => $data['contact']],
                ['name' => $data['name'], 'created_by_id' => $admin->id],
            );
        }
    }
}
