<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\ShopCache;
use Illuminate\Console\Command;

class CleanupShop extends Command
{
    protected $signature = 'shop:cleanup {--dry-run : Только показать, что будет удалено}';

    protected $description = 'Удалить неиспользуемые фото товаров и очистить кэш магазина';

    public function handle(): int
    {
        $keep = $this->collectKeepFilenames();
        $dir = public_path('images/products');
        $removed = 0;

        if (! is_dir($dir)) {
            $this->info('Папка images/products пуста.');

            return self::SUCCESS;
        }

        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (isset($keep[$file])) {
                continue;
            }

            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (! is_file($path)) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  - {$file}");
            } else {
                unlink($path);
            }
            $removed++;
        }

        if (! $this->option('dry-run')) {
            ShopCache::flush();
        }

        $this->info($this->option('dry-run')
            ? "Лишних файлов: {$removed} (запустите без --dry-run для удаления)"
            : "Удалено лишних фото: {$removed}");

        return self::SUCCESS;
    }

    /** @return array<string, true> */
    private function collectKeepFilenames(): array
    {
        $keep = [];

        $catalog = require database_path('data/catalog.php');
        foreach ($catalog['products'] ?? [] as $item) {
            $local = $item['image']['local'] ?? null;
            if ($local) {
                $keep[$local] = true;
                $base = pathinfo($local, PATHINFO_FILENAME);
                $keep[$base.'.png'] = true;
                $keep[$base.'.jpg'] = true;
                $keep[$base.'.webp'] = true;
            }
        }

        foreach (Product::query()->get(['image', 'slug']) as $product) {
            if ($product->image && ! str_starts_with($product->image, 'http')) {
                $keep[$product->image] = true;
                $base = pathinfo($product->image, PATHINFO_FILENAME);
                $keep[$base.'.png'] = true;
                $keep[$base.'.jpg'] = true;
                $keep[$base.'.webp'] = true;
            }

            if ($product->slug && str_starts_with($product->slug, 'wb-')) {
                $keep[$product->slug.'.png'] = true;
                $keep[$product->slug.'.jpg'] = true;
                $keep[$product->slug.'.webp'] = true;
            }
        }

        return $keep;
    }
}
