<?php

/**
 * Пересобрать catalog.php: только товары с совпадающими nm, названием и фото.
 */
$catalog = require dirname(__DIR__).'/data/catalog.php';
$products = $catalog['products'] ?? [];

$canonical = [];
foreach ($products as $item) {
    if (! preg_match('#/(\d{6,})/images/#', (string) ($item['image']['url'] ?? ''), $m)) {
        continue;
    }
    $nm = (int) $m[1];
    if (! isset($canonical[$nm])) {
        $canonical[$nm] = $item;
        $canonical[$nm]['nm'] = $nm;
    }
}

$out = array_values($canonical);

$categories = <<<'PHP'
    'categories' => [
        ['name' => 'Одежда', 'slug' => 'odezhda'],
        ['name' => 'Мужчинам', 'slug' => 'muzhchinam'],
        ['name' => 'Обувь', 'slug' => 'obuv'],
        ['name' => 'Аксессуары', 'slug' => 'aksessuary'],
    ],
PHP;

$content = "<?php\n\n/**\n * Каталог ModaStyle. Название и фото привязаны к артикулу WB (nm).\n */\nreturn [\n{$categories}\n    'products' => ".var_export($out, true).",\n];\n";

file_put_contents(dirname(__DIR__).'/data/catalog.php', $content);

$map = [];
foreach ($out as $item) {
    $map[$item['name']] = $item['image']['url'];
}
file_put_contents(dirname(__DIR__).'/data/product_images.php', "<?php\n\nreturn ".var_export($map, true).";\n");

echo 'Товаров в каталоге: '.count($out).PHP_EOL;
