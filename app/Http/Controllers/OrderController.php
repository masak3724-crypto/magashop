<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\RussianCities;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->get();

        return view('shop.orders', compact('orders'));
    }

    public function create()
    {
        $cart = $this->getCart()->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Корзина пуста');
        }

        $profile = auth()->user()->profile;
        $user = auth()->user();

        return view('shop.order.create', [
            'cart' => $cart,
            'cities' => RussianCities::all(),
            'initial' => [
                'first_name' => explode(' ', $user->name)[0] ?? '',
                'last_name' => explode(' ', $user->name, 2)[1] ?? '',
                'email' => $user->email,
                'address' => $profile?->address ?? '',
                'postal_code' => $profile?->postal_code ?? '',
                'city' => $profile?->city ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => ['required', 'string', 'max:50', 'regex:/^[а-яА-ЯёЁ\s\-]+$/u'],
            'email' => 'required|email',
            'address' => 'required|string|max:250',
            'postal_code' => 'required|digits:6',
            'city' => ['required', 'string', Rule::in(RussianCities::all())],
        ], [
            'last_name.regex' => 'В фамилии допускаются только буквы.',
            'postal_code.digits' => 'Индекс должен содержать ровно 6 цифр.',
            'city.in' => 'Выберите город из списка.',
        ]);

        $data['city'] = RussianCities::normalize($data['city']) ?? $data['city'];

        $cart = $this->getCart()->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart');
        }

        $order = Order::create([
            ...$data,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'paid' => false,
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
            ]);
        }

        $cart->items()->delete();

        return redirect()
            ->route('order.show', $order)
            ->with('success', 'Заказ успешно оформлен!');
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_if($order->status === 'cancelled', 404);
        $order->load('items.product.category');

        return view('shop.order.detail', compact('order'));
    }

    public function cancel(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        if ($order->status !== 'processing') {
            return redirect()->route('orders')->with('error', 'Этот заказ нельзя отменить.');
        }

        $order->update(['status' => 'cancelled', 'paid' => false]);

        return redirect()->route('orders')->with('success', 'Заказ отменён.');
    }

    public function payment(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_if($order->paid, 403);

        return view('shop.order.payment', compact('order'));
    }

    public function processPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $request->validate([
            'card_number' => ['required', 'regex:/^\d{4} \d{4} \d{4} \d{4}$/'],
            'card_name' => ['required', 'regex:/^[A-Z\s]+$/'],
            'card_expiry' => ['required', 'regex:/^\d{2}\/\d{2}$/'],
            'card_cvv' => ['required', 'digits:3'],
        ], [
            'card_number.regex' => 'Номер карты должен содержать 16 цифр в формате 0000 0000 0000 0000.',
            'card_name.regex' => 'Имя владельца — только английские буквы.',
            'card_expiry.regex' => 'Укажите срок в формате MM/YY.',
            'card_cvv.digits' => 'CVV должен содержать 3 цифры.',
        ]);

        $order->update(['paid' => true, 'status' => 'processing']);

        return redirect()
            ->route('order.show', $order)
            ->with('success', 'Оплата прошла успешно! Заказ передан в обработку.');
    }

    private function getCart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => auth()->id()]);
    }
}
