<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\DesignCode;
use App\Models\ProductFinish;
use App\Models\ProductSize;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ── Companies ────────────────────────────────────────────────────────
        $companies = [
            ['name' => 'Kajaria Ceramics',  'short_code' => 'KAJ',  'contact_person' => 'Rahul Kajaria',  'phone' => '9800000001'],
            ['name' => 'Asian Granito',     'short_code' => 'AGI',  'contact_person' => 'Anil Patel',     'phone' => '9800000002'],
            ['name' => 'Somany Ceramics',   'short_code' => 'SOM',  'contact_person' => 'Shreekant Somany','phone' => '9800000003'],
            ['name' => 'Nitco Tiles',       'short_code' => 'NIT',  'contact_person' => 'Vivek Talwar',   'phone' => '9800000004'],
            ['name' => 'RAK Ceramics',      'short_code' => 'RAK',  'contact_person' => 'Abdulla Massaad','phone' => '9800000005'],
        ];

        $companyMap = [];
        foreach ($companies as $data) {
            $company = Company::firstOrCreate(['name' => $data['name']], $data);
            $companyMap[$data['short_code']] = $company->id;
        }

        // ── Categories ───────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Vitrified Tiles',  'hsn_code' => '69072100', 'description' => 'Double charged and full body vitrified tiles'],
            ['name' => 'Ceramic Tiles',    'hsn_code' => '69071000', 'description' => 'Wall and floor ceramic tiles'],
            ['name' => 'Marble',           'hsn_code' => '25151200', 'description' => 'Natural and engineered marble slabs'],
            ['name' => 'Granite',          'hsn_code' => '25161200', 'description' => 'Natural granite slabs and tiles'],
            ['name' => 'Porcelain Slabs',  'hsn_code' => '69072200', 'description' => 'Large format porcelain slabs'],
        ];

        $categoryMap = [];
        foreach ($categories as $data) {
            $category = Category::firstOrCreate(['name' => $data['name']], $data);
            $categoryMap[$data['name']] = $category->id;
        }

        // ── Product Sizes ────────────────────────────────────────────────────
        // area_sqft = (width_value / 12) × (height_value / 12)  [values in inches → sq ft]
        $sizes = [
            ['label' => '12x12',  'width_value' => 12,  'height_value' => 12,  'unit' => 'inch', 'area_sqft' => 1.0000],
            ['label' => '16x16',  'width_value' => 16,  'height_value' => 16,  'unit' => 'inch', 'area_sqft' => 1.7778],
            ['label' => '18x18',  'width_value' => 18,  'height_value' => 18,  'unit' => 'inch', 'area_sqft' => 2.2500],
            ['label' => '24x24',  'width_value' => 24,  'height_value' => 24,  'unit' => 'inch', 'area_sqft' => 4.0000],
            ['label' => '24x48',  'width_value' => 24,  'height_value' => 48,  'unit' => 'inch', 'area_sqft' => 8.0000],
            ['label' => '32x32',  'width_value' => 32,  'height_value' => 32,  'unit' => 'inch', 'area_sqft' => 7.1111],
            ['label' => '48x48',  'width_value' => 48,  'height_value' => 48,  'unit' => 'inch', 'area_sqft' => 16.0000],
            ['label' => '800x1600', 'width_value' => 800, 'height_value' => 1600, 'unit' => 'mm', 'area_sqft' => 13.7500],
        ];

        $sizeMap = [];
        foreach ($sizes as $data) {
            $size = ProductSize::firstOrCreate(['label' => $data['label']], $data);
            $sizeMap[$data['label']] = $size->id;
        }

        // ── Product Finishes ─────────────────────────────────────────────────
        $finishes = ['Glossy', 'Matt', 'Satin', 'Polished', 'Anti-Skid', 'Rustic'];
        $finishMap = [];
        foreach ($finishes as $name) {
            $finish = ProductFinish::firstOrCreate(['name' => $name]);
            $finishMap[$name] = $finish->id;
        }

        // ── Design Codes ─────────────────────────────────────────────────────
        $designs = [
            // Kajaria Vitrified
            [
                'company_id'      => $companyMap['KAJ'],
                'category_id'     => $categoryMap['Vitrified Tiles'],
                'size_id'         => $sizeMap['24x24'],
                'finish_id'       => $finishMap['Glossy'],
                'design_name'     => 'Kajaria Whitewood',
                'design_code'     => 'KAJ-VT-2424-001',
                'thickness'       => 9,
                'purchase_price'  => 38.00,
                'sale_price'      => 52.00,
                'piece_per_box'   => 4,
                'weight_per_box_kg' => 22,
            ],
            [
                'company_id'      => $companyMap['KAJ'],
                'category_id'     => $categoryMap['Vitrified Tiles'],
                'size_id'         => $sizeMap['32x32'],
                'finish_id'       => $finishMap['Matt'],
                'design_name'     => 'Kajaria Sandstorm',
                'design_code'     => 'KAJ-VT-3232-002',
                'thickness'       => 9,
                'purchase_price'  => 42.00,
                'sale_price'      => 58.00,
                'piece_per_box'   => 2,
                'weight_per_box_kg' => 24,
            ],
            // Asian Granito Vitrified
            [
                'company_id'      => $companyMap['AGI'],
                'category_id'     => $categoryMap['Vitrified Tiles'],
                'size_id'         => $sizeMap['24x48'],
                'finish_id'       => $finishMap['Polished'],
                'design_name'     => 'AGI Statuario Gold',
                'design_code'     => 'AGI-VT-2448-001',
                'thickness'       => 10,
                'purchase_price'  => 65.00,
                'sale_price'      => 88.00,
                'piece_per_box'   => 2,
                'weight_per_box_kg' => 32,
            ],
            [
                'company_id'      => $companyMap['AGI'],
                'category_id'     => $categoryMap['Porcelain Slabs'],
                'size_id'         => $sizeMap['800x1600'],
                'finish_id'       => $finishMap['Satin'],
                'design_name'     => 'AGI Onyx Grey Large',
                'design_code'     => 'AGI-PS-8016-001',
                'thickness'       => 12,
                'purchase_price'  => 95.00,
                'sale_price'      => 130.00,
                'piece_per_box'   => 1,
                'weight_per_box_kg' => 38,
            ],
            // Somany Ceramic
            [
                'company_id'      => $companyMap['SOM'],
                'category_id'     => $categoryMap['Ceramic Tiles'],
                'size_id'         => $sizeMap['12x12'],
                'finish_id'       => $finishMap['Glossy'],
                'design_name'     => 'Somany White Gloss',
                'design_code'     => 'SOM-CT-1212-001',
                'thickness'       => 7,
                'purchase_price'  => 18.00,
                'sale_price'      => 26.00,
                'piece_per_box'   => 12,
                'weight_per_box_kg' => 18,
            ],
            [
                'company_id'      => $companyMap['SOM'],
                'category_id'     => $categoryMap['Ceramic Tiles'],
                'size_id'         => $sizeMap['18x18'],
                'finish_id'       => $finishMap['Anti-Skid'],
                'design_name'     => 'Somany Floor Rustic',
                'design_code'     => 'SOM-CT-1818-002',
                'thickness'       => 8,
                'purchase_price'  => 22.00,
                'sale_price'      => 32.00,
                'piece_per_box'   => 6,
                'weight_per_box_kg' => 20,
            ],
            // Nitco Marble
            [
                'company_id'      => $companyMap['NIT'],
                'category_id'     => $categoryMap['Marble'],
                'size_id'         => $sizeMap['24x48'],
                'finish_id'       => $finishMap['Polished'],
                'design_name'     => 'Nitco Carrara White',
                'design_code'     => 'NIT-MB-2448-001',
                'thickness'       => 15,
                'purchase_price'  => 120.00,
                'sale_price'      => 165.00,
                'piece_per_box'   => 2,
                'weight_per_box_kg' => 45,
            ],
            // RAK Porcelain
            [
                'company_id'      => $companyMap['RAK'],
                'category_id'     => $categoryMap['Porcelain Slabs'],
                'size_id'         => $sizeMap['48x48'],
                'finish_id'       => $finishMap['Matt'],
                'design_name'     => 'RAK Velvet Beige',
                'design_code'     => 'RAK-PS-4848-001',
                'thickness'       => 10,
                'purchase_price'  => 85.00,
                'sale_price'      => 118.00,
                'piece_per_box'   => 1,
                'weight_per_box_kg' => 52,
            ],
        ];

        foreach ($designs as $data) {
            DesignCode::firstOrCreate(['design_code' => $data['design_code']], $data);
        }
    }
}
