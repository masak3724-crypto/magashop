<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportFashionCatalog extends Command
{
    protected $signature = 'catalog:import-fashion {--sync-images : Скачать фото локально}';

    protected $description = 'Удалить все товары и импортировать одежду/обувь с Lamoda и oodji';

    private const OODJI_BASE = 'https://www.oodji.com';

    private const OODJI_PAGES = [
        'odezhda' => [
            'https://www.oodji.com/womens_collection/platya/',
            'https://www.oodji.com/womens_collection/dzhinsy/',
            'https://www.oodji.com/womens_collection/futbolki/',
            'https://www.oodji.com/womens_collection/bryuki-i-shorty/',
            'https://www.oodji.com/womens_collection/kurtki-i-palto/',
            'https://www.oodji.com/womens_collection/svitery/',
            'https://www.oodji.com/mens_collection/rubashki/',
            'https://www.oodji.com/mens_collection/bryuki/',
            'https://www.oodji.com/mens_collection/futbolki/',
            'https://www.oodji.com/mens_collection/kurtki/',
        ],
        'obuv' => [
            'https://www.oodji.com/womens_collection/obuv/',
            'https://www.oodji.com/mens_collection/obuv/',
        ],
    ];

    public function handle(ProductImageService $images): int
    {
        ini_set('memory_limit', '512M');

        $this->info('Сбор товаров с oodji.com и lamoda.ru...');

        $oodji = array_slice($this->fetchOodjiProducts(), 0, 35);
        $lamoda = array_slice($this->fetchLamodaProducts(), 0, 25);

        $all = array_merge($oodji, $lamoda);
        if (count($all) < 10) {
            $this->error('Слишком мало товаров для импорта.');

            return self::FAILURE;
        }

        $this->info('Найдено: oodji='.count($oodji).', lamoda='.count($lamoda));

        DB::transaction(function () use ($all, $images) {
            $this->purgeProducts();
            $this->ensureCategories();
            $this->importProducts($all, $images);
            $this->ensureAdminUser();
        });

        $this->writeCatalogFiles($all);
        $this->info('Импорт завершён. Товаров: '.Product::count());

        return self::SUCCESS;
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

        Category::query()->whereNotIn('slug', ['odezhda', 'obuv'])->delete();
    }

    private function ensureCategories(): void
    {
        Category::firstOrCreate(['slug' => 'odezhda'], ['name' => 'Одежда']);
        Category::firstOrCreate(['slug' => 'obuv'], ['name' => 'Обувь']);
    }

    /** @param list<array<string, mixed>> $items */
    private function importProducts(array $items, ProductImageService $images): void
    {
        $dir = $images->directory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($items as $item) {
            $category = Category::where('slug', $item['category'])->first();
            $slug = Str::slug($item['name']).'-'.Str::substr(md5($item['name'].$item['source']), 0, 6);
            $local = Str::slug($item['name']).'.jpg';
            $imageValue = $item['image_url'];

            if ($this->option('sync-images') && ! empty($item['image_url'])) {
                $stored = $this->downloadImage($item['image_url'], $dir.DIRECTORY_SEPARATOR.$local, $item['source']);
                if ($stored) {
                    $imageValue = $stored;
                }
            }

            Product::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => $slug,
                'price' => $item['price'],
                'description' => $item['description'] ?? $item['name'],
                'image' => $imageValue,
                'available' => true,
            ]);
        }
    }

    private function ensureAdminUser(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@magashop.local'],
            [
                'name' => 'admin',
                'password' => 'password',
                'is_admin' => true,
            ],
        );
    }

    /** @return list<array<string, mixed>> */
    private function fetchOodjiProducts(): array
    {
        $items = [];

        foreach (self::OODJI_PAGES as $category => $urls) {
            foreach ($urls as $url) {
                $html = $this->httpGet($url);
                if (! $html) {
                    $this->warn("oodji: не загружена $url");
                    continue;
                }

                foreach ($this->parseOodjiJsonLd($html) as $product) {
                    $product['category'] = $category;
                    $product['source'] = 'oodji';
                    $items[] = $product;
                }
                usleep(300000);
            }
        }

        return $this->dedupeByName($items);
    }

    /** @return list<array<string, mixed>> */
    private function parseOodjiJsonLd(string $html): array
    {
        $items = [];
        if (! preg_match_all('#"@type"\s*:\s*"Product"[\s\S]*?"name"\s*:\s*"([^"]+)"[\s\S]*?"image"\s*:\s*"([^"]+)"[\s\S]*?"price"\s*:\s*(\d+)#u', $html, $m, PREG_SET_ORDER)) {
            return $items;
        }

        foreach ($m as $match) {
            $name = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
            $path = $match[2];
            $price = (float) $match[3];
            if (str_contains($name, 'Главная') || str_contains($name, 'коллекция')) {
                continue;
            }
            $imageUrl = str_starts_with($path, 'http')
                ? $path
                : self::OODJI_BASE.(str_starts_with($path, '/') ? $path : '/'.$path);

            $items[] = [
                'name' => $name,
                'price' => max($price, 499),
                'image_url' => $imageUrl,
                'description' => $name.' — коллекция oodji.',
            ];
        }

        return $items;
    }

    /** @return list<array<string, mixed>> */
    private function fetchLamodaProducts(): array
    {
        $xml = $this->fetchGz('https://export.lamoda.ru/feed/0_461_dd3821da.xml.gz');
        if (! $xml) {
            return [];
        }

        preg_match_all('#<loc>https://www\.lamoda\.ru/p/([^/]+)/([^<]+)</loc>#', $xml, $m, PREG_SET_ORDER);
        $items = [];

        foreach ($m as $match) {
            $slug = rtrim($match[2], '/');
            if (! preg_match('#^(clothes|shoes)-([^-]+)-(.+)$#i', $slug, $parts)) {
                continue;
            }
            if (preg_match('#beauty|underwear|home-|kids-|cosmetic|swimwear-plavki#i', $slug)) {
                continue;
            }

            $name = $this->lamodaSlugToName($parts[1], $parts[2], $parts[3]);
            if (mb_strlen($name) < 6) {
                continue;
            }

            $imageUrl = $this->lamodaImageForSku(strtoupper($match[1]));
            if (! $imageUrl) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'price' => random_int(1990, 18990),
                'image_url' => $imageUrl,
                'category' => str_starts_with(strtolower($parts[1]), 'shoes') ? 'obuv' : 'odezhda',
                'source' => 'lamoda',
                'description' => $name.' — каталог Lamoda.',
            ];

            if (count($items) >= 40) {
                break;
            }
        }

        return $this->dedupeByName($items);
    }

    private function lamodaSlugToName(string $kind, string $brand, string $tail): string
    {
        $words = $this->translitWords(explode('-', $tail));
        $brandRu = $this->translitWords([$brand]);
        $brandRu = implode(' ', array_map('ucfirst', $brandRu));

        if (count($words) >= 2) {
            $noun = array_pop($words);
            $adj = implode(' ', $words);
            $title = ucfirst($noun).($adj ? ' '.implode(' ', array_map('ucfirst', explode(' ', $adj))) : '');
        } else {
            $title = ucfirst($words[0] ?? $tail);
        }

        return trim($title.' '.$brandRu);
    }

    /** @param list<string> $parts */
    private function translitWords(array $parts): array
    {
        $map = [
            'futbolka' => 'футболка', 'rubashka' => 'рубашка', 'platye' => 'платье', 'dzhinsy' => 'джинсы',
            'bryuki' => 'брюки', 'kurtka' => 'куртка', 'palto' => 'пальто', 'krossovki' => 'кроссовки',
            'botinki' => 'ботинки', 'sapogi' => 'сапоги', 'tufli' => 'туфли', 'shorty' => 'шорты',
            'yubka' => 'юбка', 'bluzka' => 'блузка', 'zhaket' => 'жакет', 'hoodie' => 'худи',
            'tolstovka' => 'толстовка', 'lofery' => 'лоферы', 'mokasiny' => 'мокасины', 'kedy' => 'кеды',
            'vetrovka' => 'ветровка', 'parka' => 'парка', 'pidzhak' => 'пиджак', 'kardigan' => 'кардиган',
            'sportivnye' => 'спортивные', 'sportivnaya' => 'спортивная', 'uteplennaya' => 'утеплённая',
            'uteplennyy' => 'утеплённый', 'pukhovik' => 'пуховик', 'slantsy' => 'слипоны',
            'plavki' => 'плавки', 'legginsy' => 'легинсы', 'top' => 'топ', 'dzhemper' => 'джемпер',
            'sviter' => 'свитер', 'chelsea' => 'челси', 'bosonozhki' => 'босоножки', 'sandali' => 'сандалии',
            'uggi' => 'угги', 'polusapogi' => 'полусапоги', 'botilony' => 'ботильоны', 'derby' => 'дерби',
            'oksfordy' => 'оксфорды', 'monki' => 'монки', 'espadrili' => 'эспадрильи', 'mokasiny' => 'мокасины',
            'adidas' => 'Adidas', 'nike' => 'Nike', 'mango' => 'Mango', 'zara' => 'Zara', 'oodji' => 'oodji',
            'reebok' => 'Reebok', 'puma' => 'Puma', 'tommy' => 'Tommy', 'hilfiger' => 'Hilfiger',
        ];

        return array_map(fn ($w) => $map[$w] ?? $w, $parts);
    }

    private function lamodaImageForSku(string $sku): ?string
    {
        static $cache = null;
        static $pool = null;

        if ($cache === null) {
            $path = database_path('data/lamoda_image_map.json');
            $cache = is_file($path)
                ? (json_decode(file_get_contents($path), true) ?: [])
                : [];
            $pool = array_values($cache);
        }

        if (isset($cache[$sku])) {
            return $cache[$sku];
        }

        $a = $sku[0];
        $b = $sku[1];
        foreach (range(11830314, 11830322) as $mid) {
            foreach ([1, 2, 3] as $photo) {
                $url = "https://a.lmcdn.ru/img600x866/{$a}/{$b}/{$sku}_{$mid}_{$photo}_v1.jpg";
                if ($this->imageExists($url)) {
                    $cache[$sku] = $url;

                    return $url;
                }
            }
        }

        return $pool[array_rand($pool)] ?? null;
    }

    private function imageExists(string $url): bool
    {
        try {
            $r = Http::timeout(8)
                ->withOptions(['verify' => false])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0', 'Referer' => 'https://www.lamoda.ru/'])
                ->get($url);

            return $r->successful() && strlen($r->body()) > 3000;
        } catch (\Throwable) {
            return false;
        }
    }

    private function httpGet(string $url): ?string
    {
        try {
            $r = Http::timeout(45)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0',
                    'Accept-Language' => 'ru-RU,ru;q=0.9',
                ])
                ->get($url);

            return $r->successful() ? $r->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function fetchGz(string $url): ?string
    {
        try {
            $r = Http::timeout(120)->withOptions(['verify' => false])->get($url);

            return $r->successful() ? gzdecode($r->body()) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function downloadImage(string $url, string $path, string $referer): ?string
    {
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
            CURLOPT_HTTPHEADER => [
                'Referer: '.($referer === 'oodji' ? 'https://www.oodji.com/' : 'https://www.lamoda.ru/'),
            ],
        ]);
        $ok = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (! $ok || $code < 200 || $code >= 400 || ! is_file($path) || filesize($path) < 2048) {
            @unlink($path);

            return null;
        }

        return basename($path);
    }

    /** @param list<array<string, mixed>> $items */
    private function dedupeByName(array $items): array
    {
        $seen = [];
        $out = [];
        foreach ($items as $item) {
            $key = mb_strtolower($item['name']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
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
                'description' => $item['description'] ?? $item['name'],
                'image' => ['local' => $local, 'url' => $item['image_url']],
            ];
            $imageMap[$item['name']] = $item['image_url'];
        }

        $catalog = "<?php\n\nreturn [\n    'categories' => [\n        ['name' => 'Одежда', 'slug' => 'odezhda'],\n        ['name' => 'Обувь', 'slug' => 'obuv'],\n    ],\n    'products' => ".$this->exportArray($catalogProducts).",\n];\n";

        File::put(database_path('data/catalog.php'), $catalog);
        File::put(database_path('data/product_images.php'), "<?php\n\nreturn ".$this->exportArray($imageMap).";\n");
        File::delete(database_path('data/marketplace_meta.php'));
    }

    private function exportArray(array $data): string
    {
        return var_export($data, true);
    }
}
