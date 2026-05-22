<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$catalog = require dirname(__DIR__).'/data/catalog.php';
$keepSlugs = [];
foreach ($catalog['products'] as $item) {
    $nm = (int) ($item['nm'] ?? 0);
    if ($nm > 0) {
        $keepSlugs[] = 'wb-'.$nm;
    }
}

$deleted = Product::query()
    ->when($keepSlugs !== [], fn ($q) => $q->whereNotIn('slug', $keepSlugs))
    ->delete();

echo "Удалено: {$deleted}, осталось: ".Product::count().PHP_EOL;
