<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductCatalogSeeder::class,
            DemoUsersSeeder::class,
        ]);
    }
}
