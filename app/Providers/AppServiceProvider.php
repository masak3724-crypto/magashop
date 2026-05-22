<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Product;
use App\Support\RailwayPostgres;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        RailwayPostgres::apply();

        Paginator::useBootstrapFive();

        View::composer('shop.layouts.app', function ($view) {
            $cartItemsCount = 0;

            if (Auth::check()) {
                try {
                    $cartItemsCount = (int) DB::table('cart_items')
                        ->join('carts', 'carts.id', '=', 'cart_items.cart_id')
                        ->where('carts.user_id', Auth::id())
                        ->sum('cart_items.quantity');
                } catch (\Throwable) {
                    $cartItemsCount = 0;
                }
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

}
