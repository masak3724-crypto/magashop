<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ShopCache
{
    private const TTL = 3600;

    /** @return Collection<int, Category> */
    public static function categories(): Collection
    {
        return static::rememberCategories('shop.categories', fn () => Category::orderBy('name')->get());
    }

    /** @return Collection<int, Category> */
    public static function categoriesWithProductCount(): Collection
    {
        return static::rememberCategories(
            'shop.categories_with_counts',
            fn () => Category::withCount('products')->orderBy('name')->get()
        );
    }

    public static function flush(): void
    {
        Cache::forget('shop.categories');
        Cache::forget('shop.categories_with_counts');
        ProductImageResolver::clearCache();
    }

    /**
     * @param  callable(): EloquentCollection<int, Category>  $resolver
     * @return Collection<int, Category>
     */
    private static function rememberCategories(string $key, callable $resolver): Collection
    {
        $payload = Cache::get($key);

        if (! static::isCategoryPayload($payload)) {
            if ($payload !== null) {
                Cache::forget($key);
            }

            $payload = static::serializeCategories($resolver());
            Cache::put($key, $payload, self::TTL);
        }

        return static::hydrateCategories($payload);
    }

    /**
     * @param  EloquentCollection<int, Category>  $categories
     * @return list<array<string, mixed>>
     */
    private static function serializeCategories(EloquentCollection $categories): array
    {
        return $categories
            ->map(fn (Category $category) => $category->getAttributes())
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $payload
     */
    private static function isCategoryPayload(mixed $payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        foreach ($payload as $row) {
            if (! is_array($row) || ! array_key_exists('id', $row) || ! array_key_exists('name', $row)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array<string, mixed>>  $payload
     * @return Collection<int, Category>
     */
    private static function hydrateCategories(array $payload): Collection
    {
        return Category::query()
            ->hydrate($payload)
            ->sortBy('name')
            ->values();
    }
}
