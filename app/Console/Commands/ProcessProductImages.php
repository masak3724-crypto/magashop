<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Console\Command;

class ProcessProductImages extends Command
{
    protected $signature = 'products:process-images';

    protected $description = 'Remove white backgrounds from existing product photos';

    public function handle(ProductImageService $images): int
    {
        if (! $images->canProcessImages()) {
            $this->error('Расширение GD не включено в PHP. Включите extension=gd в php.ini.');

            return self::FAILURE;
        }

        $dir = $images->directory();

        foreach (Product::whereNotNull('image')->get() as $product) {
            $path = $dir.DIRECTORY_SEPARATOR.$product->image;
            if (! is_file($path)) {
                $this->warn("File missing: {$product->name}");
                continue;
            }

            if (! $images->needsBackgroundRemoval($path)) {
                $this->line("Пропуск (уже прозрачный фон): {$product->name}");
                continue;
            }

            $pngName = pathinfo($product->image, PATHINFO_FILENAME).'.png';
            $pngPath = $dir.DIRECTORY_SEPARATOR.$pngName;

            $this->info("Обработка: {$product->name}");
            $tempPath = $pngPath.'.tmp';
            if (! $images->processFile($path, $tempPath)) {
                $this->warn("Не удалось обработать: {$product->name}");
                @unlink($tempPath);
                continue;
            }

            if (is_file($pngPath)) {
                unlink($pngPath);
            }
            rename($tempPath, $pngPath);

            if ($path !== $pngPath && is_file($path)) {
                unlink($path);
            }

            $product->update(['image' => $pngName]);
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
