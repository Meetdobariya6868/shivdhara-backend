<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Design;
use App\Models\DesignVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ── Companies ────────────────────────────────────────────────────────
        $companies = [
            ['company_name' => 'Kajaria Ceramics'],
            ['company_name' => 'Asian Granito'],
            ['company_name' => 'Somany Ceramics'],
            ['company_name' => 'RAK Ceramics'],
            ['company_name' => 'Nitco Tiles'],
        ];

        $companyMap = [];
        foreach ($companies as $data) {
            $company = Company::firstOrCreate(['company_name' => $data['company_name']], $data);
            $companyMap[$data['company_name']] = $company->id;
        }

        // ── Designs with variants ─────────────────────────────────────────
        $catalog = [
            [
                'company'     => 'Kajaria Ceramics',
                'design_code' => 'KAJ-001',
                'design_name' => 'Kajaria Whitewood',
                'variants'    => [
                    ['size' => '24x24', 'finish' => 'Glossy', 'thickness' => '9mm', 'purchase_rate' => 38.00, 'sell_rate' => 52.00],
                    ['size' => '24x24', 'finish' => 'Matt',   'thickness' => '9mm', 'purchase_rate' => 36.00, 'sell_rate' => 49.00],
                ],
            ],
            [
                'company'     => 'Kajaria Ceramics',
                'design_code' => 'KAJ-002',
                'design_name' => 'Kajaria Sandstorm',
                'variants'    => [
                    ['size' => '32x32', 'finish' => 'Matt',    'thickness' => '9mm',  'purchase_rate' => 42.00, 'sell_rate' => 58.00],
                    ['size' => '32x32', 'finish' => 'Glossy',  'thickness' => '9mm',  'purchase_rate' => 44.00, 'sell_rate' => 60.00],
                ],
            ],
            [
                'company'     => 'Asian Granito',
                'design_code' => 'AGI-001',
                'design_name' => 'AGI Statuario Gold',
                'variants'    => [
                    ['size' => '24x48', 'finish' => 'Polished', 'thickness' => '10mm', 'purchase_rate' => 65.00, 'sell_rate' => 88.00],
                    ['size' => '24x48', 'finish' => 'Satin',    'thickness' => '10mm', 'purchase_rate' => 62.00, 'sell_rate' => 84.00],
                ],
            ],
            [
                'company'     => 'Asian Granito',
                'design_code' => 'AGI-002',
                'design_name' => 'AGI Onyx Grey',
                'variants'    => [
                    ['size' => '800x1600', 'finish' => 'Satin', 'thickness' => '12mm', 'purchase_rate' => 95.00, 'sell_rate' => 130.00],
                ],
            ],
            [
                'company'     => 'Somany Ceramics',
                'design_code' => 'SOM-001',
                'design_name' => 'Somany White Gloss',
                'variants'    => [
                    ['size' => '12x12', 'finish' => 'Glossy',   'thickness' => '7mm', 'purchase_rate' => 18.00, 'sell_rate' => 26.00],
                    ['size' => '18x18', 'finish' => 'Anti-Skid','thickness' => '8mm', 'purchase_rate' => 22.00, 'sell_rate' => 32.00],
                ],
            ],
            [
                'company'     => 'Nitco Tiles',
                'design_code' => 'NIT-001',
                'design_name' => 'Nitco Carrara White',
                'variants'    => [
                    ['size' => '24x48', 'finish' => 'Polished', 'thickness' => '15mm', 'purchase_rate' => 120.00, 'sell_rate' => 165.00],
                ],
            ],
            [
                'company'     => 'RAK Ceramics',
                'design_code' => 'RAK-001',
                'design_name' => 'RAK Velvet Beige',
                'variants'    => [
                    ['size' => '48x48', 'finish' => 'Matt',   'thickness' => '10mm', 'purchase_rate' => 85.00,  'sell_rate' => 118.00],
                    ['size' => '24x24', 'finish' => 'Glossy', 'thickness' => '8mm',  'purchase_rate' => 48.00,  'sell_rate' => 68.00],
                ],
            ],
        ];

        foreach ($catalog as $entry) {
            $design = Design::firstOrCreate(
                [
                    'company_id'  => $companyMap[$entry['company']],
                    'design_code' => $entry['design_code'],
                ],
                [
                    'design_name' => $entry['design_name'],
                    'is_active'   => true,
                ],
            );

            foreach ($entry['variants'] as $v) {
                DesignVariant::firstOrCreate(
                    [
                        'design_id' => $design->id,
                        'size'      => $v['size'],
                        'finish'    => $v['finish'],
                        'thickness' => $v['thickness'],
                    ],
                    [
                        'purchase_rate' => $v['purchase_rate'],
                        'sell_rate'     => $v['sell_rate'],
                        'is_active'     => true,
                    ],
                );
            }
        }
    }
}
