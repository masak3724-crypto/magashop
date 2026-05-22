<?php

namespace App\Support;

class CatalogImages
{
    public static function catalog(): array
    {
        static $catalog = null;

        if ($catalog === null) {
            $catalog = require database_path('data/catalog.php');
            $urls = require database_path('data/product_images.php');

            foreach ($catalog['products'] as &$item) {
                if (isset($urls[$item['name']])) {
                    $item['image']['url'] = $urls[$item['name']];
                }
            }
            unset($item);
        }

        return $catalog;
    }

    public static function productMap(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            foreach (self::catalog()['products'] as $item) {
                $map[$item['name']] = $item['image'] ?? [];
            }
        }

        return $map;
    }

    /** @param array{local?: string, file?: string, url?: string} $image */
    public static function downloadUrl(array $image): ?string
    {
        return $image['url'] ?? null;
    }

    /** @param array{local?: string, file?: string, url?: string} $image */
    public static function previewUrl(array $image): ?string
    {
        return self::downloadUrl($image);
    }

    public static function isImageResponse(string $body, ?string $contentType): bool
    {
        if (str_contains(strtolower($contentType ?? ''), 'text/html')) {
            return false;
        }

        if (strlen($body) < 2048) {
            return false;
        }

        return str_starts_with($body, "\xFF\xD8\xFF")
            || str_starts_with($body, "\x89PNG")
            || str_starts_with($body, 'GIF8')
            || (str_starts_with($body, 'RIFF') && str_contains(substr($body, 0, 32), 'WEBP'));
    }
}
