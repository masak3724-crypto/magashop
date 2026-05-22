<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Одежда', 'slug' => 'odezhda'],
            ['name' => 'Мужчинам', 'slug' => 'muzhchinam'],
            ['name' => 'Обувь', 'slug' => 'obuv'],
            ['name' => 'Аксессуары', 'slug' => 'aksessuary'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $products = [
            ['category' => 'odezhda', 'name' => 'Платье миди на запах', 'price' => 4990, 'description' => 'Лёгкое платье из вискозы с поясом. Идеально для офиса и вечера.'],
            ['category' => 'odezhda', 'name' => 'Джинсы mom fit голубые', 'price' => 3490, 'description' => 'Плотный деним, высокая посадка, универсальный силуэт.'],
            ['category' => 'odezhda', 'name' => 'Блузка из льна с воротником', 'price' => 2790, 'description' => 'Натуральный лён, свободный крой, летняя коллекция.'],
            ['category' => 'odezhda', 'name' => 'Пальто oversize бежевое', 'price' => 12990, 'description' => 'Демисезонное пальто из смесовой шерсти, подкладка.'],
            ['category' => 'obuv', 'name' => 'Кроссовки белые на платформе', 'price' => 5990, 'description' => 'Лёгкая подошва, кожзам, повседневный стиль.'],
            ['category' => 'obuv', 'name' => 'Ботильоны на каблуке', 'price' => 7490, 'description' => 'Замша, каблук 7 см, классический фасон.'],
            ['category' => 'obuv', 'name' => 'Лоферы чёрные кожаные', 'price' => 6490, 'description' => 'Натуральная кожа, удобная колодка, офис и город.'],
            ['category' => 'aksessuary', 'name' => 'Сумка через плечо', 'price' => 3990, 'description' => 'Эко-кожа, регулируемый ремень, несколько отделений.'],
            ['category' => 'aksessuary', 'name' => 'Ремень с металлической пряжкой', 'price' => 1990, 'description' => 'Натуральная кожа, универсальный размер.'],
            ['category' => 'muzhchinam', 'name' => 'Рубашка классическая белая', 'price' => 3290, 'description' => 'Хлопок, классический крой, для офиса и повседневного образа.'],
            ['category' => 'muzhchinam', 'name' => 'Куртка кожаная чёрная', 'price' => 14990, 'description' => 'Натуральная кожа, современный крой, демисезон.'],
            ['category' => 'muzhchinam', 'name' => 'Брюки чинос бежевые', 'price' => 3990, 'description' => 'Хлопок со стрейчем, slim fit, универсальный цвет.'],
        ];

        foreach ($products as $item) {
            $category = Category::where('slug', $item['category'])->first();

            Product::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => Str::slug($item['name']).'-'.Str::random(4),
                'price' => $item['price'],
                'image' => null,
                'description' => $item['description'],
                'available' => true,
            ]);
        }

        User::updateOrCreate(
            ['email' => 'admin@modastyle.ru'],
            [
                'name' => 'admin',
                'password' => 'password',
                'is_admin' => true,
            ],
        );

        User::create([
            'name' => 'demo',
            'email' => 'demo@modastyle.ru',
            'password' => 'password',
        ])->profile()->create([
            'phone' => '+7 937 953 54 80',
            'city' => 'Чебоксары',
            'address' => 'ул. Декабристов, 17А',
            'postal_code' => '125009',
        ]);
    }
}
