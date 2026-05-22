<?php

namespace App\Support;

use App\Models\Product;

class ProductImageResolver
{
    /** @var array<string, true>|null filename => true */
    private static ?array $productFiles = null;

    public static function url(Product $product): string
    {
        if ($product->resolved_image_url !== null) {
            return $product->resolved_image_url;
        }

        $product->resolved_image_url = self::resolve($product);

        return $product->resolved_image_url;
    }

    private static function resolve(Product $product): string
    {
        $image = $product->image;

        if (! $image) {
            return self::placeholder();
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        foreach (self::candidates($image, $product->slug) as $filename) {
            if (isset(self::fileIndex()[$filename])) {
                return asset('images/products/'.$filename);
            }
        }

        if (isset(self::fileIndex()[$image]) && is_string(self::fileIndex()[$image])) {
            return asset('images/products/'.self::fileIndex()[$image]);
        }

        $catalogUrl = self::catalogFallback($product->name);
        if ($catalogUrl) {
            return $catalogUrl;
        }

        return self::placeholder();
    }

    /** @return list<string> */
    private static function candidates(string $image, ?string $slug): array
    {
        $base = pathinfo($image, PATHINFO_FILENAME);
        $candidates = [$image, $base.'.png', $base.'.jpg', $base.'.webp'];

        if ($slug && str_starts_with($slug, 'wb-')) {
            $candidates[] = $slug.'.png';
            $candidates[] = $slug.'.jpg';
            $candidates[] = $slug.'.webp';
        }

        return array_values(array_unique($candidates));
    }

    /** @return array<string, string|true> */
    private static function fileIndex(): array
    {
        if (self::$productFiles !== null) {
            return self::$productFiles;
        }

        self::$productFiles = [];
        $dir = public_path('images/products');

        if (! is_dir($dir)) {
            return self::$productFiles;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$entry;
            if (! is_file($path) || filesize($path) < 512) {
                continue;
            }
            self::$productFiles[$entry] = true;
            self::$productFiles[pathinfo($entry, PATHINFO_FILENAME)] = $entry;
        }

        return self::$productFiles;
    }

    public static function clearCache(): void
    {
        self::$productFiles = null;
    }

    private static function catalogFallback(string $name): ?string
    {
        $image = CatalogImages::productMap()[$name] ?? null;

        return $image ? CatalogImages::previewUrl($image) : null;
    }

    private static function placeholder(): string
    {
        return asset('images/clothing-placeholder.svg');
    }
}
