<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class AssignProductImages extends Command
{
    protected $signature = 'products:assign-images';

    protected $description = 'Assign local image filenames to products when files exist';

    /** @var array<string, string> */
    private const MAP = [
        'Платье миди на запах' => 'platye-midi-zapah.jpg',
        'Джинсы mom fit голубые' => 'dzhinsy-mom-fit.jpg',
        'Блузка из льна с воротником' => 'bluzka-len.jpg',
        'Пальто oversize бежевое' => 'palto-oversize.jpg',
        'Кроссовки белые на платформе' => 'krossovki-belye.jpg',
        'Ботильоны на каблуке' => 'botilony-kabluk.jpg',
        'Лоферы чёрные кожаные' => 'lofery-chernye.jpg',
        'Сумка через плечо' => 'sumka-cherez-plecho.jpg',
        'Ремень с металлической пряжкой' => 'remen-pryazhka.jpg',
        'Рубашка классическая белая' => 'rubashka-belaya.jpg',
        'Куртка кожаная чёрная' => 'kurtka-kozhanaya.jpg',
        'Брюки чинос бежевые' => 'bryuki-chinos.jpg',
    ];

    public function handle(): int
    {
        $dir = public_path('images/products');
        $updated = 0;

        foreach (self::MAP as $name => $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (! is_file($path) || filesize($path) < 1024) {
                $this->warn("Missing: {$name} ({$file})");
                continue;
            }

            $count = Product::where('name', $name)->update(['image' => $file]);
            if ($count) {
                $this->info("{$name} → {$file}");
                $updated++;
            }
        }

        $this->info("Updated {$updated} product(s).");

        return self::SUCCESS;
    }
}
