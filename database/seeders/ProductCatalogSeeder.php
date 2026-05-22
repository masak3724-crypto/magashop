<?php

namespace Database\Seeders;

use App\Support\CatalogSync;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $stats = CatalogSync::fromCatalogFile(prune: false);

        $this->command?->info(sprintf(
            'Каталог: %d категорий, %d товаров (идемпотентно).',
            $stats['categories'],
            $stats['products'],
        ));
    }
}
