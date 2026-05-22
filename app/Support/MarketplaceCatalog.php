<?php

namespace App\Support;

class MarketplaceCatalog
{
    public static function metaMap(): array
    {
        static $map = null;

        if ($map === null) {
            $path = database_path('data/marketplace_meta.php');
            $map = is_file($path) ? require $path : [];
        }

        return $map;
    }

    public static function meta(string $productName): array
    {
        $defaults = [
            'source' => 'lamoda',
            'brand' => 'ModaStyle',
            'old_price' => null,
            'discount' => 0,
            'rating' => 4.5,
            'reviews' => 0,
        ];

        return array_merge($defaults, self::metaMap()[$productName] ?? []);
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'wildberries' => 'Wildberries',
            'oodji' => 'oodji',
            default => 'Lamoda',
        };
    }
}
