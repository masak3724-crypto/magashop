<?php

namespace Database\Seeders;

use App\Support\CatalogSync;
use Illuminate\Database\Seeder;

/**
 * Идемпотентное наполнение для Railway / production.
 * Безопасно после каждого deploy: php artisan migrate --force && db:seed --class=RailwaySeeder
 */
class RailwaySeeder extends Seeder
{
    public function run(): void
    {
        $stats = CatalogSync::fromCatalogFile(prune: false);

        $this->command?->info(sprintf(
            'Railway: %d категорий, %d товаров (идемпотентно).',
            $stats['categories'],
            $stats['products'],
        ));

        $this->call(DemoUsersSeeder::class);
    }
}
