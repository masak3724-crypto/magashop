<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class CatalogSync
{
    /**
     * Синхронизация каталога из database/data/catalog.php (без сетевых запросов).
     *
     * @return array{products: int, categories: int, pruned: int}
     */
    public static function fromCatalogFile(bool $prune = false): array
    {
        $catalog = require database_path('data/catalog.php');
        $categories = $catalog['categories'] ?? [];
        $products = $catalog['products'] ?? [];

        $categoryCount = self::syncCategories($categories);
        $productCount = self::syncProducts($products);

        $pruned = $prune ? self::pruneOrphans($products) : 0;

        if ($productCount > 0 || $pruned > 0) {
            ShopCache::flush();
        }

        return [
            'categories' => $categoryCount,
            'products' => $productCount,
            'pruned' => $pruned,
        ];
    }

    /** @param list<array{name: string, slug: string}> $categories */
    private static function syncCategories(array $categories): int
    {
        $count = 0;

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name']],
            );
            $count++;
        }

        return $count;
    }

    /** @param list<array<string, mixed>> $products */
    private static function syncProducts(array $products): int
    {
        $count = 0;

        foreach ($products as $item) {
            if (self::upsertProduct($item)) {
                $count++;
            }
        }

        return $count;
    }

    /** @param array<string, mixed> $item */
    public static function upsertProduct(array $item): bool
    {
        $category = Category::query()->where('slug', $item['category'] ?? '')->first();

        if (! $category) {
            return false;
        }

        $nm = (int) ($item['nm'] ?? 0);
        $name = (string) ($item['name'] ?? '');
        $slug = $nm > 0
            ? 'wb-'.$nm
            : Str::slug($name).'-'.Str::substr(md5($name), 0, 4);

        $key = $nm > 0 ? ['slug' => $slug] : ['name' => $name];

        Product::updateOrCreate(
            $key,
            [
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'price' => (int) ($item['price'] ?? 0),
                'description' => (string) ($item['description'] ?? ''),
                'image' => $item['image']['local'] ?? null,
                'available' => true,
            ],
        );

        return true;
    }

    /** @param list<array<string, mixed>> $catalogProducts */
    public static function pruneOrphans(array $catalogProducts): int
    {
        $slugs = [];

        foreach ($catalogProducts as $item) {
            $nm = (int) ($item['nm'] ?? 0);
            if ($nm > 0) {
                $slugs[] = 'wb-'.$nm;
            }
        }

        if ($slugs !== []) {
            return Product::query()->whereNotIn('slug', $slugs)->delete();
        }

        $names = array_column($catalogProducts, 'name');

        return Product::query()->whereNotIn('name', $names)->delete();
    }
}
