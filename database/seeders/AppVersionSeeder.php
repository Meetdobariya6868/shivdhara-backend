<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    public function run(): void
    {
        $versions = [
            [
                'version'       => '1.0.0',
                'platform'      => 'both',
                'is_latest'     => false,
                'force_update'  => false,
                'release_notes' => 'Initial release.',
            ],
            [
                'version'       => '1.1.0',
                'platform'      => 'android',
                'is_latest'     => false,
                'force_update'  => false,
                'release_notes' => 'Bug fixes and performance improvements.',
            ],
            [
                'version'       => '1.2.0',
                'platform'      => 'android',
                'is_latest'     => true,
                'force_update'  => false,
                'release_notes' => 'Order status tracking added. UI improvements.',
            ],
            [
                'version'       => '1.2.0',
                'platform'      => 'ios',
                'is_latest'     => true,
                'force_update'  => false,
                'release_notes' => 'Order status tracking added. UI improvements.',
            ],
        ];

        foreach ($versions as $data) {
            AppVersion::firstOrCreate(
                ['version' => $data['version'], 'platform' => $data['platform']],
                $data,
            );
        }
    }
}
