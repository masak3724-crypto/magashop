<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ShopCache
{
    private const TTL = 3600;

    /** @return Collection<int, Category> */
    public static function categories(): Collection
    {
        return Cache::remember('shop.categories', self::TTL, fn () => Category::orderBy('name')->get());
    }

    /** @return Collection<int, Category> */
    public static function categoriesWithProductCount(): Collection
    {
        return Cache::remember('shop.categories_with_counts', self::TTL, fn () => Category::withCount('products')->orderBy('name')->get());
    }

    public static function flush(): void
    {
        Cache::forget('shop.categories');
        Cache::forget('shop.categories_with_counts');
        ProductImageResolver::clearCache();
    }
}
