<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name'    => 'Rajesh Kumar',
                'phone'   => '9876543201',
                'email'   => 'rajesh@gmail.com',
                'address' => '12, Navrangpura, Ahmedabad',
                'city'    => 'Ahmedabad',
                'state'   => 'Gujarat',
                'pincode' => '380001',
            ],
            [
                'name'       => 'Sunita Patel',
                'phone'      => '9876543202',
                'address'    => '45, Ring Road, Surat',
                'city'       => 'Surat',
                'state'      => 'Gujarat',
                'pincode'    => '395003',
            ],
            [
                'name'       => 'Mohan Shah',
                'phone'      => '9876543203',
                'email'      => 'mohan@yahoo.com',
                'address'    => '8, Alkapuri, Vadodara',
                'city'       => 'Vadodara',
                'state'      => 'Gujarat',
                'pincode'    => '390001',
                'gst_number' => '24AAAAA0000A1Z5',
            ],
            [
                'name'    => 'Priti Desai',
                'phone'   => '9876543204',
                'address' => '23, Kalavad Road, Rajkot',
                'city'    => 'Rajkot',
                'state'   => 'Gujarat',
                'pincode' => '360001',
            ],
            [
                'name'       => 'Arvind Mehta',
                'phone'      => '9876543205',
                'email'      => 'arvind@gmail.com',
                'address'    => '5, Marine Lines, Mumbai',
                'city'       => 'Mumbai',
                'state'      => 'Maharashtra',
                'pincode'    => '400001',
                'gst_number' => '27BBBBB0000B1Z5',
            ],
            [
                'name'    => 'Kavitha Nair',
                'phone'   => '9876543206',
                'address' => '77, Satellite Road, Ahmedabad',
                'city'    => 'Ahmedabad',
                'state'   => 'Gujarat',
                'pincode' => '380015',
            ],
            [
                'name'    => 'Deepak Sharma',
                'phone'   => '9876543207',
                'email'   => 'deepak@outlook.com',
                'address' => '3, MI Road, Jaipur',
                'city'    => 'Jaipur',
                'state'   => 'Rajasthan',
                'pincode' => '302001',
            ],
            [
                'name'    => 'Neha Joshi',
                'phone'   => '9876543208',
                'address' => '18, FC Road, Pune',
                'city'    => 'Pune',
                'state'   => 'Maharashtra',
                'pincode' => '411001',
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(['phone' => $data['phone']], $data);
        }
    }
}
