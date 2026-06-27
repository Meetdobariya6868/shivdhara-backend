<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,       // no FK deps
            CustomerSeeder::class,   // no FK deps
            ArchitectSeeder::class,  // no FK deps
            CatalogSeeder::class,    // companies → categories → sizes → finishes → design_codes
            OrderSeeder::class,      // orders → order_items → order_status_history → payments
            AppVersionSeeder::class, // standalone
        ]);
    }
}
