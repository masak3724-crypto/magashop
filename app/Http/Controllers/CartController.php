<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart()->load(['items.product.category']);

        return view('shop.cart', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:100']);

        $cart = $this->getCart();
        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity', (int) $request->input('quantity', 1));
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => (int) $request->input('quantity', 1),
            ]);
        }

        return redirect()
            ->route('product.show', $product)
            ->with('success', "Товар «{$product->name}» добавлен в корзину");
    }

    public function update(Request $request, CartItem $item)
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $quantity = (int) $request->input('quantity', 1);

        if ($quantity > 0) {
            $item->update(['quantity' => $quantity]);
            return redirect()->route('cart')->with('success', 'Количество обновлено');
        }

        $item->delete();

        return redirect()->route('cart')->with('success', 'Товар удалён из корзины');
    }

    public function remove(CartItem $item)
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);
        $item->delete();

        return redirect()->route('cart')->with('success', 'Товар удалён из корзины');
    }

    private function getCart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => auth()->id()]);
    }
}
