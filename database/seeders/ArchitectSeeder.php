<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Architect;
use Illuminate\Database\Seeder;

class ArchitectSeeder extends Seeder
{
    public function run(): void
    {
        $architects = [
            [
                'name'      => 'Hiren Trivedi',
                'phone'     => '9700000101',
                'email'     => 'hiren@trivediarch.com',
                'firm_name' => 'Trivedi Architects',
                'city'      => 'Ahmedabad',
            ],
            [
                'name'      => 'Sonal Kapoor',
                'phone'     => '9700000102',
                'email'     => 'sonal@kapoordesigns.in',
                'firm_name' => 'Kapoor Design Studio',
                'city'      => 'Mumbai',
            ],
            [
                'name'      => 'Vijay Bhatt',
                'phone'     => '9700000103',
                'firm_name' => 'VB Architecture',
                'city'      => 'Surat',
            ],
            [
                'name'      => 'Meera Iyer',
                'phone'     => '9700000104',
                'email'     => 'meera@iyerinteriors.com',
                'firm_name' => 'Iyer Interiors',
                'city'      => 'Bangalore',
            ],
        ];

        foreach ($architects as $data) {
            Architect::firstOrCreate(['phone' => $data['phone']], $data);
        }
    }
}
