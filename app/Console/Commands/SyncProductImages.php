<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageService;
use App\Support\CatalogImages;
use App\Support\ShopCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncProductImages extends Command
{
    protected $signature = 'products:sync-images
                            {--force : Перезагрузить даже если файл уже есть}
                            {--only-failed : Только товары без локального фото}';

    protected $description = 'Скачать уникальное фото для каждого товара';

    public function handle(ProductImageService $images): int
    {
        ini_set('memory_limit', '512M');

        $map = CatalogImages::productMap();
        $dir = $images->directory();

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $force = $this->option('force');
        $onlyFailed = $this->option('only-failed');
        $failed = 0;
        $total = Product::count();
        $index = 0;

        $this->info("Загрузка фото для {$total} товаров (у каждого своё изображение)...");

        foreach (Product::with('category')->orderBy('id')->get() as $product) {
            $index++;
            $mapping = $map[$product->name] ?? null;

            if (! $mapping || empty($mapping['local']) || empty($mapping['url'])) {
                $this->warn("[{$index}/{$total}] Нет URL: {$product->name}");
                $failed++;
                continue;
            }

            $local = $mapping['local'];
            $path = $dir.DIRECTORY_SEPARATOR.$local;
            $pngPath = $dir.DIRECTORY_SEPARATOR.pathinfo($local, PATHINFO_FILENAME).'.png';

            if ($onlyFailed && (is_file($path) || is_file($pngPath))) {
                continue;
            }

            if (! $force && ((is_file($path) && filesize($path) > 2048) || (is_file($pngPath) && filesize($pngPath) > 2048))) {
                $stored = is_file($pngPath) ? pathinfo($local, PATHINFO_FILENAME).'.png' : $local;
                if ($product->image !== $stored) {
                    $product->update(['image' => $stored]);
                }
                continue;
            }

            $this->line("[{$index}/{$total}] {$product->name}");

            if (is_file($path)) {
                unlink($path);
            }
            if (is_file($pngPath)) {
                unlink($pngPath);
            }

            $sources = array_filter([
                $mapping['url'] ?? null,
                'https://picsum.photos/seed/'.md5($product->name).'/800/1000',
            ]);

            $body = null;
            foreach ($sources as $url) {
                $body = $this->fetchImage($url);
                if ($body !== null) {
                    break;
                }
            }

            if ($body === null) {
                $remote = $mapping['url'] ?? null;
                if ($remote && str_starts_with($remote, 'http')) {
                    $product->update(['image' => $remote]);
                    $this->warn('  CDN → сохранён URL в БД');
                } else {
                    $this->error('  не удалось скачать');
                    $failed++;
                }
                sleep(2);
                continue;
            }

            file_put_contents($path, $body);

            if (! $this->isValidImageFile($path)) {
                @unlink($path);
                $this->error('  файл не является изображением');
                $failed++;
                continue;
            }

            $this->shrinkImageIfNeeded($path, 900);

            $stored = $local;
            if ($images->needsBackgroundRemoval($path)) {
                $pngLocal = pathinfo($local, PATHINFO_FILENAME).'.png';
                $pngFull = $dir.DIRECTORY_SEPARATOR.$pngLocal;
                $tempPath = $pngFull.'.tmp';
                try {
                    if ($images->processFile($path, $tempPath) && is_file($tempPath)) {
                        if (is_file($pngFull)) {
                            unlink($pngFull);
                        }
                        rename($tempPath, $pngFull);
                        unlink($path);
                        $stored = $pngLocal;
                    } else {
                        @unlink($tempPath);
                    }
                } catch (\Throwable) {
                    @unlink($tempPath);
                }
            }

            $product->update(['image' => $stored]);
            $this->info("  OK → {$stored}");
            sleep(1);
        }

        if ($failed > 0) {
            $this->warn("Готово с {$failed} ошибкой(ами). Повторите: php artisan products:sync-images --only-failed");

            return self::FAILURE;
        }

        ShopCache::flush();
        $this->info('Все фото загружены — у каждого товара своё изображение.');

        return self::SUCCESS;
    }

    private function fetchImage(string $url): ?string
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::timeout(90)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                        'Referer' => str_contains($url, 'wbbasket') ? 'https://www.wildberries.ru/' : (str_contains($url, 'lmcdn') ? 'https://www.lamoda.ru/' : 'https://www.oodji.com/'),
                    ])
                    ->withOptions(['verify' => false, 'allow_redirects' => true])
                    ->get($url);
            } catch (\Throwable $e) {
                $this->warn("  попытка {$attempt}: {$e->getMessage()}");
                sleep(2 * $attempt);
                continue;
            }

            if (! $response->successful()) {
                $this->warn("  HTTP {$response->status()}");
                sleep(2);
                continue;
            }

            $body = $response->body();
            if (CatalogImages::isImageResponse($body, $response->header('Content-Type'))) {
                return $body;
            }

            sleep(2);
        }

        return null;
    }

    private function isValidImageFile(string $path): bool
    {
        $info = @getimagesize($path);

        return $info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true);
    }

    private function shrinkImageIfNeeded(string $path, int $maxSide): void
    {
        if (! function_exists('imagecreatefromjpeg')) {
            return;
        }

        $info = @getimagesize($path);
        if ($info === false) {
            return;
        }

        [$width, $height] = $info;
        if ($width <= $maxSide && $height <= $maxSide) {
            return;
        }

        $ratio = min($maxSide / $width, $maxSide / $height);
        $newW = (int) round($width * $ratio);
        $newH = (int) round($height * $ratio);

        $source = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($source === false) {
            return;
        }

        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($source);

        match ($info[2]) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $path, 88),
            IMAGETYPE_PNG => imagepng($thumb, $path),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($thumb, $path, 88) : null,
            default => null,
        };

        imagedestroy($thumb);
    }
}
