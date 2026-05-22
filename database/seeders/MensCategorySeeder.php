<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MensCategorySeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'muzhchinam'],
            ['name' => 'Мужчинам'],
        );

        $products = [
            ['name' => 'Рубашка классическая белая', 'price' => 3290, 'description' => 'Хлопок, классический крой, для офиса и повседневного образа.'],
            ['name' => 'Куртка кожаная чёрная', 'price' => 14990, 'description' => 'Натуральная кожа, современный крой, демисезон.'],
            ['name' => 'Брюки чинос бежевые', 'price' => 3990, 'description' => 'Хлопок со стрейчем, slim fit, универсальный цвет.'],
        ];

        foreach ($products as $item) {
            Product::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'slug' => Str::slug($item['name']).'-'.Str::random(4),
                    'price' => $item['price'],
                    'image' => null,
                    'description' => $item['description'],
                    'available' => true,
                ],
            );
        }
    }
}
