<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = require database_path('data/catalog.php');

        \Illuminate\Support\Facades\DB::table('order_items')->delete();
        \Illuminate\Support\Facades\DB::table('cart_items')->delete();
        Product::query()->delete();

        $keepSlugs = array_column($catalog['categories'], 'slug');
        Category::query()->whereNotIn('slug', $keepSlugs)->delete();

        foreach ($catalog['categories'] as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], ['name' => $cat['name']]);
        }

        foreach ($catalog['products'] as $item) {
            $category = Category::where('slug', $item['category'])->first();

            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'slug' => Str::slug($item['name']).'-'.Str::substr(md5($item['name']), 0, 4),
                    'price' => $item['price'],
                    'image' => $item['image']['local'] ?? null,
                    'description' => $item['description'],
                    'available' => true,
                ],
            );
        }
    }
}
