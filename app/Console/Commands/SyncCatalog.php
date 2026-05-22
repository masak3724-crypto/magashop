<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogSync;
use App\Support\ShopCache;
use App\Support\Wildberries;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncCatalog extends Command
{
    protected $signature = 'catalog:sync
                            {--append-wb : Добавить новые товары из Wildberries (без удаления существующих)}
                            {--download-images : Скачать фото для товаров без локального файла}
                            {--prune : Удалить из БД товары, которых нет в catalog.php}';

    protected $description = 'Синхронизировать каталог из database/data/catalog.php';

    public function handle(): int
    {
        $catalog = require database_path('data/catalog.php');
        $products = $catalog['products'] ?? [];

        if ($this->option('download-images')) {
            $this->ensureCategories($catalog['categories'] ?? []);
            $synced = 0;
            foreach ($products as $item) {
                if ($this->upsertFromCatalogItem($item)) {
                    $synced++;
                }
            }
            $this->info("Из catalog.php: {$synced} товаров.");
        } else {
            $stats = CatalogSync::fromCatalogFile(prune: $this->option('prune'));
            $this->info("Из catalog.php: {$stats['products']} товаров.");
            if ($this->option('prune') && $stats['pruned'] > 0) {
                $this->info("Удалено из БД: {$stats['pruned']} товаров без записи в каталоге.");
            }
        }

        if ($this->option('append-wb')) {
            $added = $this->appendFromWildberries();
            $this->info("Из Wildberries: добавлено {$added} новых товаров.");
        }

        if ($this->option('prune') && $this->option('download-images')) {
            $removed = CatalogSync::pruneOrphans($products);
            if ($removed > 0) {
                $this->info("Удалено из БД: {$removed} товаров без записи в каталоге.");
            }
        }

        $this->rebuildProductImagesMap();
        ShopCache::flush();

        $this->info('Всего в магазине: '.Product::count().' товаров.');

        return self::SUCCESS;
    }

    private function rebuildProductImagesMap(): void
    {
        $catalog = require database_path('data/catalog.php');
        $map = [];

        foreach ($catalog['products'] ?? [] as $item) {
            $url = $item['image']['url'] ?? null;
            if ($url) {
                $map[$item['name']] = $url;
            }
        }

        $export = var_export($map, true);
        File::put(database_path('data/product_images.php'), "<?php\n\nreturn {$export};\n");
    }

    /** @param list<array{name: string, slug: string}> $categories */
    private function ensureCategories(array $categories): void
    {
        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], ['name' => $cat['name']]);
        }
    }

    /** @param array<string, mixed> $item */
    private function upsertFromCatalogItem(array $item): bool
    {
        $category = Category::where('slug', $item['category'] ?? '')->first();
        if (! $category) {
            return false;
        }

        $nm = (int) ($item['nm'] ?? 0);
        if ($nm <= 0 && ! empty($item['image']['url'])) {
            $nm = $this->extractNmFromImageUrl((string) $item['image']['url']) ?? 0;
        }

        $name = $item['name'];
        $description = $item['description'] ?? '';
        $imageUrl = $item['image']['url'] ?? null;

        if ($nm > 0) {
            $card = Wildberries::fetchCard($nm);
            if ($card) {
                $name = Wildberries::productName($card);
                $description = Wildberries::genericDescription($name, $item['category'] ?? 'odezhda');
                $imageUrl = Wildberries::imageUrl($nm, 1);
            }
        }

        $slug = $nm > 0
            ? 'wb-'.$nm
            : Str::slug($name).'-'.Str::substr(md5($name), 0, 4);
        $image = $item['image']['local'] ?? null;

        if ($this->option('download-images') && $imageUrl) {
            $downloaded = $this->downloadImage($imageUrl, $slug.'.jpg');
            if ($downloaded) {
                $image = $downloaded;
            }
        }

        Product::updateOrCreate(
            $nm > 0 ? ['slug' => $slug] : ['name' => $name],
            [
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'price' => (int) ($item['price'] ?? 0),
                'description' => $description,
                'image' => $image,
                'available' => true,
            ],
        );

        return true;
    }

    private function extractNmFromImageUrl(string $url): ?int
    {
        if (preg_match('#/(\d{6,})/images/#', $url, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function appendFromWildberries(): int
    {
        $seed = require database_path('data/wb_nm_seed.php');
        $added = 0;

        foreach ($seed as $entry) {
            $nm = (int) ($entry['nm'] ?? 0);
            if ($nm <= 0) {
                continue;
            }

            $card = Wildberries::fetchCard($nm);
            if (! $card) {
                $this->warn("Пропуск {$nm}: card.json недоступен");

                continue;
            }

            $name = Wildberries::productName($card);
            if ($this->shouldSkipWildberriesProduct($name, $card) || Product::where('name', $name)->exists()) {
                continue;
            }

            $categorySlug = $entry['category'] ?? Wildberries::guessCategory($card);
            $category = Category::where('slug', $categorySlug)->first();
            if (! $category) {
                continue;
            }

            $slug = Str::slug($name).'-'.Str::substr((string) $nm, -4);
            $imageUrl = Wildberries::imageUrl($nm, 1);
            $image = $this->downloadImage($imageUrl, $slug.'.jpg') ?: $imageUrl;

            Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'price' => random_int(1990, 16990),
                'description' => Wildberries::genericDescription($name, $categorySlug),
                'image' => $image,
                'available' => true,
            ]);

            $this->line("  + {$name}");
            $added++;
            usleep(80000);
        }

        return $added;
    }

    private function shouldSkipWildberriesProduct(string $name, array $card): bool
    {
        if (mb_strlen($name) < 4) {
            return true;
        }

        if (preg_match('/^(товар\s+\d+|удалить)$/iu', $name)) {
            return true;
        }

        $hay = mb_strtolower($name.' '.($card['subj_name'] ?? '').' '.($card['subj_root_name'] ?? ''));
        if (preg_match('/автомобил|запчаст|приманк|конфет|пазл|краска для волос|банкнот|постер аниме|колготк.*дет|ползунок.*малыш|проволок|мыло|гвозд|сверл|насос|фильтр/u', $hay)) {
            return true;
        }

        return false;
    }

    private function downloadImage(string $url, string $filename): ?string
    {
        $dir = public_path('images/products');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir.DIRECTORY_SEPARATOR.$filename;
        if (is_file($path) && filesize($path) > 2048) {
            return $filename;
        }

        $fp = fopen($path, 'w+b');
        if ($fp === false) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => ['Referer: https://www.wildberries.ru/'],
        ]);
        $ok = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (! $ok || $code < 200 || $code >= 400 || ! is_file($path) || filesize($path) < 2048) {
            @unlink($path);

            return null;
        }

        return $filename;
    }
}
