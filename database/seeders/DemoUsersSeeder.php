<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@modastyle.ru'],
            [
                'name' => 'admin',
                'password' => 'password',
                'is_admin' => true,
            ],
        );

        $demo = User::updateOrCreate(
            ['email' => 'demo@modastyle.ru'],
            [
                'name' => 'demo',
                'password' => 'password',
            ],
        );

        $demo->profile()->updateOrCreate(
            ['user_id' => $demo->id],
            [
                'phone' => '+7 937 953 54 80',
                'city' => 'Чебоксары',
                'address' => 'ул. Декабристов, 17А',
                'postal_code' => '428000',
            ],
        );
    }
}
