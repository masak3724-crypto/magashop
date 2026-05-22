<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\Wildberries;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportWildberriesCatalog extends Command
{
    protected $signature = 'catalog:import-wildberries';

    protected $description = 'Удалить все товары и импортировать каталог с Wildberries (название и фото)';

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $seed = require database_path('data/wb_nm_seed.php');
        $this->info('Загрузка карточек Wildberries ('.count($seed).' артикулов)...');

        $items = [];
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
            if ($this->shouldSkip($name, $card)) {
                $this->warn("Пропуск {$nm}: «{$name}»");

                continue;
            }

            $category = $entry['category'] ?? Wildberries::guessCategory($card);
            $imageUrl = $this->resolveImageUrl($nm);

            $items[] = [
                'nm' => $nm,
                'name' => $name,
                'category' => $category,
                'price' => random_int(1290, 18990),
                'image_url' => $imageUrl,
                'description' => Wildberries::genericDescription($name, $category),
            ];

            $this->line("  + {$name}");
            usleep(80000);
        }

        if (count($items) < 8) {
            $this->error('Слишком мало товаров для импорта.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($items) {
            $this->purgeProducts();
            $this->ensureCategories();
            $this->importProducts($items);
            $this->ensureAdminUser();
        });

        $this->writeCatalogFiles($items);
        $this->info('Импорт завершён. Товаров: '.Product::count());

        return self::SUCCESS;
    }

    private function shouldSkip(string $name, array $card): bool
    {
        if (mb_strlen($name) < 4) {
            return true;
        }

        if (preg_match('/^(товар\s+\d+|удалить)$/iu', $name)) {
            return true;
        }

        $hay = mb_strtolower($name.' '.($card['subj_name'] ?? '').' '.($card['subj_root_name'] ?? ''));
        if (preg_match('/автомобил|запчаст|приманк|конфет|пазл|краска для волос|банкнот|постер аниме|колготк.*дет|ползунок.*малыш/u', $hay)) {
            return true;
        }

        return false;
    }

    private function resolveImageUrl(int $nm): string
    {
        foreach ([1, 2, 3] as $photo) {
            $url = Wildberries::imageUrl($nm, $photo);
            try {
                $r = Http::timeout(15)
                    ->withOptions(['verify' => false])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0',
                        'Referer' => 'https://www.wildberries.ru/',
                    ])
                    ->get($url);

                if ($r->successful() && strlen($r->body()) > 4000) {
                    return $url;
                }
            } catch (\Throwable) {
            }
        }

        return Wildberries::imageUrl($nm, 1);
    }

    private function purgeProducts(): void
    {
        $dir = public_path('images/products');
        if (is_dir($dir)) {
            foreach (glob($dir.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        DB::table('order_items')->delete();
        DB::table('cart_items')->delete();
        Product::query()->delete();
    }

    private function ensureCategories(): void
    {
        $categories = [
            ['name' => 'Одежда', 'slug' => 'odezhda'],
            ['name' => 'Мужчинам', 'slug' => 'muzhchinam'],
            ['name' => 'Обувь', 'slug' => 'obuv'],
            ['name' => 'Аксессуары', 'slug' => 'aksessuary'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], ['name' => $cat['name']]);
        }
    }

    /** @param list<array<string, mixed>> $items */
    private function importProducts(array $items): void
    {
        $dir = public_path('images/products');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($items as $item) {
            $category = Category::where('slug', $item['category'])->first();
            if (! $category) {
                continue;
            }

            $slug = Str::slug($item['name']).'-'.Str::substr((string) $item['nm'], -4);
            $local = $slug.'.jpg';
            $path = $dir.DIRECTORY_SEPARATOR.$local;

            $stored = $this->downloadImage($item['image_url'], $path)
                ? $local
                : $item['image_url'];

            Product::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => $slug,
                'price' => $item['price'],
                'description' => $item['description'],
                'image' => $stored,
                'available' => true,
            ]);
        }
    }

    private function downloadImage(string $url, string $path): bool
    {
        $fp = fopen($path, 'w+b');
        if ($fp === false) {
            return false;
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

            return false;
        }

        return true;
    }

    private function ensureAdminUser(): void
    {
        User::query()->where('is_admin', true)->where('email', '!=', 'admin@modastyle.ru')->delete();

        User::updateOrCreate(
            ['email' => 'admin@modastyle.ru'],
            [
                'name' => 'admin',
                'password' => 'password',
                'is_admin' => true,
            ],
        );
    }

    /** @param list<array<string, mixed>> $items */
    private function writeCatalogFiles(array $items): void
    {
        $catalogProducts = [];
        $imageMap = [];

        foreach ($items as $item) {
            $local = Str::slug($item['name']).'.jpg';
            $catalogProducts[] = [
                'category' => $item['category'],
                'name' => $item['name'],
                'price' => (int) $item['price'],
                'description' => $item['description'],
                'image' => ['local' => $local, 'url' => $item['image_url']],
            ];
            $imageMap[$item['name']] = $item['image_url'];
        }

        $categories = <<<'PHP'
    'categories' => [
        ['name' => 'Одежда', 'slug' => 'odezhda'],
        ['name' => 'Мужчинам', 'slug' => 'muzhchinam'],
        ['name' => 'Обувь', 'slug' => 'obuv'],
        ['name' => 'Аксессуары', 'slug' => 'aksessuary'],
    ],
PHP;

        $catalog = "<?php\n\n/**\n * Каталог ModaStyle.\n */\nreturn [\n{$categories}\n    'products' => ".$this->exportArray($catalogProducts).",\n];\n";

        File::put(database_path('data/catalog.php'), $catalog);
        File::put(database_path('data/product_images.php'), "<?php\n\nreturn ".$this->exportArray($imageMap).";\n");
        File::delete(database_path('data/marketplace_meta.php'));
    }

    private function exportArray(array $data): string
    {
        return var_export($data, true);
    }
}
