<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRailway();

        Paginator::useBootstrapFive();

        View::composer('shop.layouts.app', function ($view) {
            $cartItemsCount = 0;

            if (Auth::check()) {
                $cartItemsCount = (int) DB::table('cart_items')
                    ->join('carts', 'carts.id', '=', 'cart_items.cart_id')
                    ->where('carts.user_id', Auth::id())
                    ->sum('cart_items.quantity');
            }

            $activeCategory = null;

            if (request()->routeIs('products')) {
                if (request()->boolean('sale')) {
                    $activeCategory = 'sale';
                } elseif (request()->has('category')) {
                    $activeCategory = request()->query('category');
                } else {
                    $activeCategory = 'all';
                }
            } elseif (request()->routeIs('product.show')) {
                $product = request()->route('product');
                if ($product instanceof Product) {
                    $product->loadMissing('category');
                    $activeCategory = $product->category->slug;
                }
            }

            $view->with([
                'cartItemsCount' => $cartItemsCount,
                'activeCategory' => $activeCategory,
            ]);
        });
    }

    private function configureRailway(): void
    {
        if (env('DATABASE_URL') && ! env('DB_URL')) {
            putenv('DB_URL='.env('DATABASE_URL'));
            $_ENV['DB_URL'] = env('DATABASE_URL');
            $_SERVER['DB_URL'] = env('DATABASE_URL');
        }

        if (env('RAILWAY_ENVIRONMENT') || env('RAILWAY_PUBLIC_DOMAIN')) {
            URL::forceScheme('https');

            if ($domain = env('RAILWAY_PUBLIC_DOMAIN')) {
                config(['app.url' => 'https://'.$domain]);
            }
        }
    }
}
