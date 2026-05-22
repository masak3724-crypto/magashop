<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\ShopCache;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->query('category', 'all');
        $products = Product::query()->where('available', true)->with('category');

        if ($request->boolean('sale')) {
            $products->where('price', '<', 3500);
        } elseif ($categorySlug !== 'all') {
            $products->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $currentCategoryModel = $categorySlug !== 'all'
            ? Category::where('slug', $categorySlug)->first()
            : null;

        return view('shop.products', [
            'products' => $products->latest()->get(),
            'categories' => ShopCache::categories(),
            'currentCategory' => $categorySlug,
            'currentCategoryLabel' => $currentCategoryModel?->name ?? 'Все',
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->available, 404);

        $relatedProducts = Product::query()
            ->with('category')
            ->where('category_id', $product->category_id)
            ->where('available', true)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('shop.product_detail', compact('product', 'relatedProducts'));
    }
}
