<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class RailwaySeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $this->call(ShopSeeder::class);
    }
}
